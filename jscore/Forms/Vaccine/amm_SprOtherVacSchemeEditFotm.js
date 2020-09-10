/**
 * amm_SprOtherVacSchemeEditFotm - окно просмотра и редактирования справочника вакцин.
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      VAC
 * @access       public
 * @copyright    Copyright (c) 2011 Swan Ltd.
 * @author       Nigmatullin Tagir (Ufa)
 * @version      11.05.2011
 */

var formsParams_OnGripp;

sw.Promed.amm_SprOtherVacSchemeEditFotm = Ext.extend(sw.Promed.BaseForm, {
    id: 'amm_SprOtherVacSchemeEditFotm',
    title: "Справочник'Схема вакцинации': Редактирование",
    titleBase: "Справочник'Схема вакцинации': Редактирование",
    codeRefresh: true,
    width: 600,
    //height: 735,
    //autoHeight: true,
    // autohight: true,
    maximizable: true,
    modal: true,
    layout: 'border',
    border: false,
    closeAction: 'hide',
    objectName: 'amm_SprOtherVacSchemeEditFotm',
    objectSrc: '/jscore/Forms/Vaccine/amm_SprOtherVacSchemeEditFotm.js',
    onHide: Ext.emptyFn,
    buttons:
            [
                {
                    text: BTN_FRMSAVE,
                    iconCls: 'save16',
                    tabIndex: TABINDEX_PRESVACEDITFRM + 31,
                    handler: function() {
                        var EditWin = Ext.getCmp('amm_SprOtherVacSchemeEditFotm');
                        var EditForm = Ext.getCmp('sprVaccineOtherEditFormPanel');
                        if (!EditForm.form.isValid()) {
                            sw.Promed.vac.utils.msgBoxNoValidForm();
                            return false;
                        }
//					return false;

//					var vacFormPanel = Ext.getCmp('vacPurpEditForm');
                        var vaccineParamsUpd = new Object();
                        vaccineParamsUpd.Vaccine_id = EditWin.formParams.Vaccine_id;
                        
                        vaccineParamsUpd.Multiplicity1 = Ext.getCmp('VacDopCh_DopKol1').getValue();
                        if (vaccineParamsUpd.Multiplicity1 > 1) {
                            vaccineParamsUpd.Interval1 = Ext.getCmp('VacDopCh_DopInterval1').getValue();
                        };
                        if (Ext.getCmp('VacDopCh_Risk1').getValue()) {
                            vaccineParamsUpd.MultiplicityRisk1 = Ext.getCmp('VacDopCh_DopKolRisk1').getValue();
                            vaccineParamsUpd.IntervalRisk1 = Ext.getCmp('VacDopCh_DopIntervalRisk1').getValue();
                        };
                        if (Ext.getCmp('VacDopCh_AgeVac').getValue()) {
                             vaccineParamsUpd.AgeTypeS1 =  Ext.getCmp('VacDopCh_AgeTypePeriodCombo1').getValue();
                            vaccineParamsUpd.AgeS1 = Ext.getCmp('VacDopCh_DopAreaRange11').getValue();
                            vaccineParamsUpd.AgeTypeS2 = Ext.getCmp('VacDopCh_AgeTypePeriodCombo2').getValue();
                            vaccineParamsUpd.AgeS2 = Ext.getCmp('VacDopCh_DopAreaRange21').getValue();
                            //vaccineParamsUpd.AgeTypeE1 = EditWin.formParams.AgeTypeE1;
                            vaccineParamsUpd.AgeE1 = Ext.getCmp('VacDopCh_DopAreaRange12').getValue();
                            //vaccineParamsUpd.AgeTypeE2 = EditWin.formParams.AgeTypeE2;
                            vaccineParamsUpd.AgeE2 = Ext.getCmp('VacDopCh_DopAreaRange22').getValue();

                            vaccineParamsUpd.Multiplicity2 = Ext.getCmp('VacDopCh_DopKol2').getValue();

                             if (vaccineParamsUpd.Multiplicity2 > 1) {
                                  vaccineParamsUpd.Interval2 = Ext.getCmp('VacDopCh_DopInterval2').getValue();
                             };

                             if (Ext.getCmp('VacDopCh_Risk2').getValue()) {
                                vaccineParamsUpd.MultiplicityRisk2 = Ext.getCmp('VacDopCh_DopKolRisk2').getValue();
                                vaccineParamsUpd.IntervalRisk2 = Ext.getCmp('VacDopCh_DopIntervalRisk2').getValue();
                            }
                         
                        }
          
                        
                        //vaccineParamsUpd.PeriodTypeInterval1 = EditWin.formParams.PeriodTypeInterval1;
                        
                        //vaccineParamsUpd.PeriodTypeInterval2 = EditWin.formParams.PeriodTypeInterval2;
                        //vaccineParamsUpd.PeriodTypeIntervalRisk1 = EditWin.formParams.PeriodTypeIntervalRisk1;
                        
                        //vaccineParamsUpd.PeriodTypeIntervalRisk2 = EditWin.formParams.PeriodTypeIntervalRisk2;
                        
                       
                        Ext.Ajax.request({
                            url: '/?c=VaccineCtrl&m=saveSprOtherVacScheme',
                            method: 'POST',
                            params: vaccineParamsUpd,
                            success: function(response, opts) {
//							if (vaccineParamsUpd.action == 'add'  ){
//              sw.Promed.vac.utils.consoleLog(response.responseText.rows);
                                if (response.responseText.length > 0) {
                                    var result = Ext.util.JSON.decode(response.responseText);
                                    sw.Promed.vac.utils.consoleLog(result.rows[0]);
//                  sw.Promed.vac.utils.consoleLog(result.rows[0].NewVacPresence_id);
//    							Ext.getCmp('amm_PresenceVacForm').fireEvent('success', result.rows[0].NewVacPresence_id);
//                                                                    if (!result.success) {
//                                                                        sw.Promed.vac.utils.consoleLog(result.rows.ddd.NewVacPresence_id);
//                                                                         alert(result.rows.ddd.NewVacPresence_id);
//                                                                    }
                                }
//							}
//							else Ext.getCmp('amm_PresenceVacForm').fireEvent('success', vaccineParamsUpd.VacPresence_id);
                                if (sw.Promed.vac.utils.msgBoxErrorBd(response) == 0) {
                                    sw.Promed.vac.utils.consoleLog('this:');
                                    sw.Promed.vac.utils.consoleLog(this);
//                Ext.getCmp(this.formParams.parent_id).fireEvent('success', 'amm_SprVaccineEditWindow', {});
                                    //alert('fireEvent');
                                    var parend_id = 'amm_SprVaccineEditWindow';
//                                    var win = Ext.getCmp('amm_SprVaccineEditWindow');
//                                    win.fireEvent('success', 'amm_SprOtherVacSchemeEditFotm', {});
// ViewFrameOtherVacScheme
                                    
                                    Ext.getCmp('ViewFrameOtherVacScheme').fireEvent('success', 'amm_SprOtherVacSchemeEditFotm', {});
                                    //Ext.getCmp('amm_SprVaccineEditWindow').fireEvent('success', 'amm_SprOtherVacSchemeEditFotm');
                                }
                                EditWin.hide();
                            }.createDelegate(this)

                        });
                    }
                },
                {
                    text: '-'
                },
//		HelpButton(this, -1)
                {text: BTN_FRMHELP,
                    iconCls: 'help16',
                    handler: function(button, event)
                    {
                        ShowHelp(this.ownerCt.titleBase);
                    }
                }
                , {
                    text: BTN_FRMCLOSE,
                    tabIndex: -1,
                    tooltip: 'Закрыть окно',
                    iconCls: 'cancel16',
                    handler: function()
                    {
                        this.ownerCt.hide();
                    }
                }
            ],

    initComponent: function() {

        /*
         * хранилище для доп сведений
         */
 
        this.formStore = new Ext.data.JsonStore({
            fields: [
                   'Vaccine_id'
                  ,'Vaccine_Name',
                  ,'Vaccine_Nick' 
                  ,'AgeTypeS1'
                  ,'AgeS1'
                  ,'AgeTypeS2'
                  ,'AgeS2'
                  ,'AgeTypeE1'
                  ,'AgeE1'
                  ,'AgeTypeE2'
                  ,'AgeE2'
                  ,'multiplicity1'
                  ,'multiplicity2'
                  ,'multiplicityRisk1'
                  ,'multiplicityRisk2'
                  ,'PeriodTypeInterval1'
                  ,'Interval1'
                  ,'PeriodTypeInterval2'
                  ,'Interval2'
                  ,'PeriodTypeIntervalRisk1'
                  ,'IntervalRisk1'
                  ,'PeriodTypeIntervalRisk2'
                  ,'IntervalRisk2'
            ],
            url: '/?c=VaccineCtrl&m=loadSprOtherVacFormInfo',
            key: 'xxx_id',
            root: 'data'
        });

        this.FormPanel = new Ext.form.FormPanel({
            bodyStyle: 'padding: 5px',
            border: true,
            frame: true,
            autoScroll: true,
            id: 'sprVaccineOtherEditFormPanel',
            autohight: true,
            region: 'center',
            autoHeight: true,
            items: [{
                    disabled: true,
                    fieldLabel: 'Наименование вакцины',
                    id: 'VacDopCh_VaccineName',
                    name: 'VacDopCh_VaccineName',
                    width: 400,
                    autoHeight: false,
                    height: 40,
                    xtype: 'textarea',
                    validator: function(value) {
                        if (value.length > 200)
                            return 'Превышена максимальная длина поля (200 символов)';
                        else
                            return true;
                    }
                },
                {
                    fieldLabel: 'Краткое наименование',
                    id: 'VacDopCh_VaccineNick',
                    name: 'VacDopCh_VaccineNick',
                    width: 400,
                    disabled: true,
                    xtype: 'textfield',
                    validator: function(value) {
                        if (value.length > 20)
                            return 'Превышена максимальная длина поля (20 символов)';
                        else
                            return true;
                    }
                },
                {
                    autoHeight: true,
                    autoScroll: true,
                    style: 'padding: 0px 5px;',
                    title: 'Схема вакцинации',
                    id: 'VacDopCh_OnGripp',
                    //height: 95,
                    labelWidth: 30,
                    autoHeight: true,
                            xtype: 'fieldset',
                    items: [
                        {
                            xtype: 'checkbox',
                            height: 24,
                            id: 'VacDopCh_AgeVac',
                            labelSeparator: '',
                            checked: true,
                            boxLabel: 'Зависит от возраста пациента',
                            listeners: {
                                'check': function(checkbox, checked) {
                                    //alert (checked);
                                    if (checked == undefined)
                                       checked = false;
                                   Ext.getCmp('VacDopCh_DopAreaRange11').setContainerVisible(checked);
                                   Ext.getCmp('VacDopCh_AgeTypePeriodCombo1').setContainerVisible(checked);
                                   
                                   //alert(checked);

                                     Ext.getCmp('VacDopCh_DopKol2').allowBlank = !checked;
                                        Ext.getCmp('VacDopCh_DopInterval2').allowBlank = !checked;
                                        Ext.getCmp('VacDopCh_DopKolRisk2').allowBlank = !checked; 
                                        Ext.getCmp('VacDopCh_DopIntervalRisk2').allowBlank = !checked; 
                                        Ext.getCmp('VacDopCh_AgeTypePeriodCombo2').allowBlank = !checked; 
                                        Ext.getCmp('VacDopCh_DopAreaRange11').allowBlank = !checked;
                                        Ext.getCmp('VacDopCh_DopAreaRange12').allowBlank = !checked;
                                        Ext.getCmp('VacDopCh_DopAreaRange21').allowBlank = !checked;
                                        Ext.getCmp('VacDopCh_DopAreaRange22').allowBlank = !checked;
                                        Ext.getCmp('VacDopCh_AgeTypePeriodCombo1').allowBlank = !checked;
                                        if (!checked) {
                                            Ext.getCmp('VacDopCh_AgeTypePeriodCombo1').allowBlank = true;
                                        }
                                        else {
                                            
                                        }
//                                     if (checked == true) {
//                                       //alert('enable');
//                                        Ext.getCmp('VacDopCh_AgeTypePeriodCombo1').enable();
//                                    } else {
//                                        //alert('disable'); 
//                                        Ext.getCmp('VacDopCh_AgeTypePeriodCombo1').disable();
//                                    };
                                    /*
//                                    Ext.getCmp('VacDopCh_AgeTypePeriodForm1').setVisible(checked)
//                                    if (checked){
//                                        Ext.getCmp('VacDopCh_AgeTypePeriodCombo1').fieldLabel = '';
//                                    }
                                        
                                    //Ext.getCmp('VacDopCh_DopAreaRange11').setDisabled(checked);
                                    */
                                    Ext.getCmp('VacDopCh_DopAreaRange12').setContainerVisible(checked);
                                    Ext.getCmp('VacDopCh_AgeGroup2').setVisible(checked);
                                    Ext.getCmp('amm_SprOtherVacSchemeEditFotm').syncShadow();//перерисовка тени под изменившееся окно
                                    
//                                    Ext.getCmp('VacDopCh_DopAreaRange21').setContainerVisible(checked);
//                                    Ext.getCmp('VacDopCh_DopAreaRange22').setContainerVisible(checked);
//                                    Ext.getCmp('VacDopCh_DopKol2').setContainerVisible(checked);
//                                    Ext.getCmp('VacDopCh_DopInterval2').setContainerVisible(checked);
                                }
                            }
                        },
                                //  Первый возрастной диапазон 
                        {
                            autoHeight: true,
                            autoScroll: true,
                            id:'VacDopCh_AgeGroup1',
                            style: 'padding: 0px 5px;',
                            height: 100,
                            labelWidth: 100,
                            xtype: 'fieldset',
                            items: [
                                {
                                    height: 10,
                                    border: false,
                                    cls: 'tg-label'
                                },
//                                {
//                                    border: false,
//                                    layout: 'column',
//                                    id: 'VacDopCh_AgeTypePeriodForm1',
//                                    width: 200,
//                                    labelWidth: 80,
//                                    items: [
                                 {
//                                                fieldLabel: 'Номер схемы',
                                    id: 'VacDopCh_AgeTypePeriodCombo1',
                                    name: 'VacDopCh_AgeTypePeriodCombo1',
                                    //disabled: true,
                                    autoLoad: false,
                                    listWidth: 100,
                                    width: 100,
                                    labelSeparator: '',
                                    //width: 150,
                                    editable: false,
                                    allowBlank: false,
                                    xtype: 'amm_AgeTypeCombo'
//                                    listeners: {
//                                        'select': function(combo, record, index) {
//                                            Ext.getCmp('amm_SprNacCalEditWindow').loadAgeTypePeriodCombo(Ext.getCmp('NC_Period').getValue(), combo.getValue())
//                                        }.createDelegate(this)
//                                    }
//                                    }]
                                },
                                {
                                    height: 5,
                                    border: false,
                                    cls: 'tg-label'
                                },
                                {
                                    border: false,
                                    layout: 'column',
                                    labelWidth: 80,
                                    items: [{
                                            border: false,
                                            layout: 'form',
                                            width: 120,
                                            items: [{
                                                    fieldLabel: 'от ',
                                                    layout: 'form',
                                                    id: 'VacDopCh_DopAreaRange11',
                                                    maskRe: /[0-9]/,
                                                    width: 30,
                                                    labelSeparator: '',
                                                    //allowBlank: false,
                                                    xtype: 'textfield'
                                                }]
                                        },
                                        {
                                            border: false,
                                            layout: 'form',
                                            labelWidth: 20,
                                            width: 90,
                                            items: [{
                                                    fieldLabel: ' до',
                                                    layout: 'form',
                                                    id: 'VacDopCh_DopAreaRange12',
                                                    maskRe: /[0-9]/,
                                                    //name: 'VacDopCh_DopAreaRange2',
                                                    width: 30,
                                                    labelSeparator: '',
                                                    allowBlank: false,
                                                    xtype: 'textfield'
                                                }]
                                        },
                                        {
                                            autoHeight: true,
                                            autoScroll: true,
                                            style: 'padding: 0px 5px;',
                                            border: false,
                                            height: 100,
                                            labelWidth: 30,
                                            xtype: 'fieldset',
                                            items: [
                                                {
                                                    border: false,
                                                    layout: 'column',
                                                    labelWidth: 50,
                                                    items: [
                                                        {
                                                            border: false,
                                                            layout: 'form',
                                                            labelWidth: 100,
                                                            width: 140,
                                                            items: [{
                                                                    fieldLabel: 'кратность',
                                                                    layout: 'form',
                                                                    id: 'VacDopCh_DopKol1',
                                                                    maskRe: /[0-9]/,
                                                                    width: 30,
                                                                    labelSeparator: '',
                                                                    allowBlank: false,
                                                                    xtype: 'textfield',
                                                                    listeners: {
                                                                         'change': function(field, newValue, oldValue) {
                                                                             if (newValue == 1) {
                                                                                 Ext.getCmp('VacDopCh_DopInterval1').setContainerVisible(false);
                                                                                 Ext.getCmp('VacDopCh_DopInterval1').allowBlank = true  
                                                                             } else {
                                                                                  Ext.getCmp('VacDopCh_DopInterval1').setContainerVisible(true);
                                                                                 Ext.getCmp('VacDopCh_DopInterval1').allowBlank = false
                                                                             }
                                                                                    
                                                                         }.createDelegate(this)
                                                                    }
                                                                }
                                                            ]
                                                        },
                                                        {
                                                            border: false,
                                                            layout: 'form',
                                                            labelWidth: 100,
                                                            width: 140,
                                                            items: [{
                                                                    fieldLabel: 'интервал (дн.)',
                                                                    layout: 'form',
                                                                    id: 'VacDopCh_DopInterval1',
                                                                    maskRe: /[0-9]/,
                                                                    width: 30,
                                                                    labelSeparator: '',
                                                                    allowBlank: false,
                                                                    xtype: 'textfield'
                                                                }]
                                                        }
                                                    ]}, 
                                                {
                                                    border: false,
                                                    layout: 'form',
                                                    items: [
                                                        {
                                                            xtype: 'checkbox',
                                                            height: 24,
                                                            id: 'VacDopCh_Risk1',
                                                            labelSeparator: '',
                                                            checked: true,
                                                            boxLabel: 'Группа риска',
                                                            listeners: {
                                                                'check': function(checkbox, checked) {
                                                                    Ext.getCmp('VacDopCh_DopKolRisk1').setContainerVisible(checked);
                                                                    Ext.getCmp('VacDopCh_DopKolRisk1').allowBlank = !checked  
                                                                    Ext.getCmp('VacDopCh_DopIntervalRisk1').setContainerVisible(checked);
                                                                    Ext.getCmp('VacDopCh_DopIntervalRisk1').allowBlank = !checked  
                                                                    Ext.getCmp('amm_SprOtherVacSchemeEditFotm').syncShadow();//перерисовка тени под изменившееся окно

                                                                }
                                                            }
                                                        }]
                                                },
                                                {
                                                    border: false,
                                                    xtype: 'panel',
                                                    layout: 'column',
                                                    labelWidth: 50,
                                                    items: [
                                                        {
                                                            border: false,
                                                            layout: 'form',
                                                            labelWidth: 100,
                                                            width: 140,
                                                            items: [{
                                                                    fieldLabel: 'кратность',
                                                                    layout: 'form',
                                                                    labelWidth: 100,
                                                                    id: 'VacDopCh_DopKolRisk1',
                                                                    maskRe: /[0-9]/,
                                                                    width: 30,
                                                                    labelSeparator: '',
                                                                    allowBlank: false,
                                                                    xtype: 'textfield',
                                                                    enableKeyEvents : true,
                                                                    listeners: {
                                                                        'keyup': function( e ) {
                                                                          var val = Ext.getCmp('VacDopCh_DopKolRisk1');
                                                                            if (val.getValue() == '0') {
                                                                                //Ext.Msg.alert('Внимание', 'Значение не должно быть равным "0"!');
                                                                                val.setValue('');
                                                                                //val.focus(true, 100);
                                                                            }
                                                                            console.log( Ext.getCmp('VacDopCh_DopKolRisk1').getValue());
                                                                        },
                                                                        'change': function(field, newValue, oldValue) {
                                                                             //alert('0');
                                                                             if (newValue == 1) {
                                                                                 Ext.getCmp('VacDopCh_DopIntervalRisk1').setContainerVisible(false);
                                                                                 Ext.getCmp('VacDopCh_DopIntervalRisk1').allowBlank = true  
                                                                             } else {
                                                                                  Ext.getCmp('VacDopCh_DopIntervalRisk1').setContainerVisible(true);
                                                                                 Ext.getCmp('VacDopCh_DopIntervalRisk1').allowBlank = false
                                                                             }
                                                                                    
                                                                         }.createDelegate(this)
                                                                    }
                                                                }]
                                                        },
                                                        {
                                                            border: false,
                                                            layout: 'form',
                                                            labelWidth: 100,
                                                            width: 140,
                                                            items: [{
                                                                    fieldLabel: 'интервал (дн.)',
                                                                    id: 'VacDopCh_DopIntervalRisk1',
                                                                    maskRe: /[0-9]/,
                                                                    width: 30,
                                                                    labelSeparator: '',
                                                                    allowBlank: false,
                                                                    xtype: 'textfield'
                                                                }]
                                                        }
                                                    ]}
                                            ]}
                                    ]}  

                            ]
                        },
                        //  Второй возрастной диапазон 
                        {
                            autoHeight: true,
                            autoScroll: true,
                            id:'VacDopCh_AgeGroup2',
                            style: 'padding: 0px 5px;',
                            height: 100,
                            labelWidth: 100,
                            xtype: 'fieldset',
                            items: [
                                {
                                    height: 10,
                                    border: false,
                                    cls: 'tg-label'
                                },
                                {
//                                                fieldLabel: 'Номер схемы',
                                    id: 'VacDopCh_AgeTypePeriodCombo2',
                                    autoLoad: false,
                                    listWidth: 100,
                                    width: 100,
                                    labelSeparator: '',
                                    //width: 150,
                                    editable: false,
                                     allowBlank: false,
                                    xtype: 'amm_AgeTypeCombo'
//                                    listeners: {
//                                        'select': function(combo, record, index) {
//                                            Ext.getCmp('amm_SprNacCalEditWindow').loadAgeTypePeriodCombo(Ext.getCmp('NC_Period').getValue(), combo.getValue())
//                                        }.createDelegate(this)
//                                    }
                                },
                                {
                                    height: 5,
                                    border: false,
                                    cls: 'tg-label'
                                },
                                {
                                    border: false,
                                    layout: 'column',
                                    labelWidth: 80,
                                    items: [{
                                            border: false,
                                            layout: 'form',
                                           width: 120,
                                            items: [{
                                                    fieldLabel: 'от ',
                                                    layout: 'form',
                                                    id: 'VacDopCh_DopAreaRange21',
                                                    maskRe: /[0-9]/,
                                                    width: 30,
                                                    labelSeparator: '',
                                                    //allowBlank: false,
                                                    xtype: 'textfield'
                                                }]
                                        },
                                        {
                                            border: false,
                                            layout: 'form',
                                            //labelAlign: 'left',
                                            labelWidth: 20,
                                            width: 90,
                                            items: [{
                                                    fieldLabel: ' до   ',
                                                    layout: 'form',
                                                    //style: 'padding-left',
                                                    id: 'VacDopCh_DopAreaRange22',
                                                    maskRe: /[0-9]/,
                                                    //name: 'VacDopCh_DopAreaRange2',
                                                    width: 30,
                                                    labelSeparator: '',
                                                    allowBlank: false,
                                                    xtype: 'textfield'
                                                }]
                                        },
                                        {
                                            autoHeight: true,
                                            autoScroll: true,
                                            style: 'padding: 0px 5px;',
                                            border: false,
                                            height: 100,
                                            labelWidth: 30,
                                            xtype: 'fieldset',
                                            items: [
                                                {
                                                    border: false,
                                                    layout: 'column',
                                                    labelWidth: 50,
                                                    items: [
                                                        {
                                                            border: false,
                                                            layout: 'form',
                                                            labelWidth: 100,
                                                            width: 140,
                                                            items: [{
                                                                    fieldLabel: 'кратность',
                                                                    layout: 'form',
                                                                    id: 'VacDopCh_DopKol2',
                                                                    maskRe: /[0-9]/,
                                                                    width: 30,
                                                                    labelSeparator: '',
                                                                    allowBlank: false,
                                                                    xtype: 'textfield',
                                                                    listeners: {
                                                                         'change': function(field, newValue, oldValue) {
                                                                             //alert('1');
                                                                             if (newValue == 1) {
                                                                                 Ext.getCmp('VacDopCh_DopInterval2').setContainerVisible(false);
                                                                                 Ext.getCmp('VacDopCh_DopInterval2').allowBlank = true  
                                                                             } else {
                                                                                  Ext.getCmp('VacDopCh_DopInterval2').setContainerVisible(true);
                                                                                 Ext.getCmp('VacDopCh_DopInterval2').allowBlank = false
                                                                             }
                                                                                    
                                                                         }.createDelegate(this)
                                                                    }
                                                                }]
                                                        },
                                                        {
                                                            border: false,
                                                            layout: 'form',
                                                            labelWidth: 100,
                                                            width: 140,
                                                            items: [{
                                                                    fieldLabel: 'интервал (дн.)',
                                                                    layout: 'form',
                                                                    id: 'VacDopCh_DopInterval2',
                                                                    maskRe: /[0-9]/,
                                                                    width: 30,
                                                                    labelSeparator: '',
                                                                    allowBlank: false,
                                                                    xtype: 'textfield'
                                                                }]
                                                        }
                                                    ]}, 
                                                {
                                                    border: false,
                                                    layout: 'form',
                                                    items: [
                                                        {
                                                            xtype: 'checkbox',
                                                            height: 24,
                                                            id: 'VacDopCh_Risk2',
                                                            labelSeparator: '',
                                                            checked: true,
                                                            boxLabel: 'Группа риска',
                                                            listeners: {
                                                                'check': function(checkbox, checked) {
                                                                    Ext.getCmp('VacDopCh_DopKolRisk2').setContainerVisible(checked);
                                                                    Ext.getCmp('VacDopCh_DopKolRisk2').allowBlank = !checked  
                                                                    Ext.getCmp('VacDopCh_DopIntervalRisk2').setContainerVisible(checked);
                                                                    Ext.getCmp('VacDopCh_DopIntervalRisk2').allowBlank = !checked  
                                                                    Ext.getCmp('amm_SprOtherVacSchemeEditFotm').syncShadow();//перерисовка тени под изменившееся окно

                                                                }
                                                            }
                                                        }]
                                                },
                                                {
                                                    border: false,
                                                    xtype: 'panel',
                                                    layout: 'column',
                                                    labelWidth: 50,
                                                    items: [
                                                        {
                                                            border: false,
                                                            layout: 'form',
                                                            labelWidth: 100,
                                                            width: 140,
                                                            items: [{
                                                                    fieldLabel: 'кратность',
                                                                    labelWidth: 100,
                                                                    maskRe: /[0-9]/,
                                                                    id: 'VacDopCh_DopKolRisk2',
                                                                    width: 30,
                                                                    labelSeparator: '',
                                                                    allowBlank: false,
                                                                    xtype: 'textfield',
                                                                    listeners: {
                                                                         'change': function(field, newValue, oldValue) {
                                                                             //alert('1');
                                                                             if (newValue == 1) {
                                                                                 Ext.getCmp('VacDopCh_DopIntervalRisk2').setContainerVisible(false);
                                                                                 Ext.getCmp('VacDopCh_DopIntervalRisk2').allowBlank = true  
                                                                             } else {
                                                                                  Ext.getCmp('VacDopCh_DopIntervalRisk2').setContainerVisible(true);
                                                                                 Ext.getCmp('VacDopCh_DopIntervalRisk2').allowBlank = false
                                                                             }
                                                                                    
                                                                         }.createDelegate(this)
                                                                    }
                                                                }]
                                                        },
                                                        {
                                                            border: false,
                                                            layout: 'form',
                                                            labelWidth: 100,
                                                            width: 140,
                                                            items: [{
                                                                    fieldLabel: 'интервал (дн.)',
                                                                    id: 'VacDopCh_DopIntervalRisk2',
                                                                    maskRe: /[0-9]/,
                                                                    width: 30,
                                                                    labelSeparator: '',
                                                                    allowBlank: false,
                                                                    xtype: 'textfield'
                                                                }]
                                                        }
                                                    ]}
                                            ]}
                                    ]}  

                            ]}
                       /*
                        {
                            border: false,
                            layout: 'column',
                            labelWidth: 50,
                            items: [{
                                    border: false,
                                    layout: 'form',
                                    items: [{
                                            fieldLabel: 'после ',
                                            layout: 'form',
                                            id: 'VacDopCh_DopAreaRange21',
                                            width: 30,
                                            labelSeparator: '',
                                            allowBlank: false,
                                            xtype: 'textfield'
                                        }]
                                },
                                {
                                    border: false,
                                    layout: 'form',
                                    labelWidth: 50,
                                    items: [{
                                            fieldLabel: 'мес.',
                                            layout: 'form',
                                            id: 'VacDopCh_DopAreaRange22',
                                            width: 30,
                                            labelSeparator: '',
                                            allowBlank: false,
                                            xtype: 'textfield'
                                        }]
                                },
                                {
                                    border: false,
                                    layout: 'form',
                                    labelWidth: 100,
                                    items: [{
                                            fieldLabel: 'кратность',
                                            layout: 'form',
                                            id: 'VacDopCh_DopKol2',
                                            width: 30,
                                            labelSeparator: '',
                                            allowBlank: false,
                                            xtype: 'textfield'
                                        }]
                                },
                                {
                                    border: false,
                                    layout: 'form',
                                    labelWidth: 100,
                                    items: [{
                                            fieldLabel: 'интервал (дн.)',
                                            layout: 'form',
                                            id: 'VacDopCh_DopInterval2',
                                            width: 30,
                                            labelSeparator: '',
                                            allowBlank: false,
                                            xtype: 'textfield'
                                        }]
                                }
                            ]
                        }
                        */
                    ]}
            ],
            labelAlign: 'right',
            labelWidth: 120
        });

        Ext.apply(this, {
            formParams: null,
            frame: true,
            //labelWidth : 150,  
            bodyBorder: true,
            layout: "form",
            cls: 'tg-label',
            autoHeight: true,
            items: [
                this.FormPanel
            ]
        });
        sw.Promed.amm_SprOtherVacSchemeEditFotm.superclass.initComponent.apply(this, arguments);
    },
    show: function(record) {

        sw.Promed.amm_SprOtherVacSchemeEditFotm.superclass.show.apply(this, arguments);
        this.formParams = record;
        if (record.Vaccine_id == undefined)
            Ext.getCmp('amm_SprOtherVacSchemeEditFotm').setTitle("Справочник'Схема вакцинации': Добавление");
        else {
            formsParams_Vaccine_id = record.Vaccine_id;
            Ext.getCmp('amm_SprVaccineEditWindow').setTitle("Справочник'Схема вакцинации': Редактирование");
            
//           this.formParams.Vaccine_Name = record.Vaccine_Name;
//           this.formParams.Vaccine_Nick = record.Vaccine_Nick; 
           
            sw.Promed.vac.utils.consoleLog('this.formParams.Vaccine_Name');
            sw.Promed.vac.utils.consoleLog(this.formParams.Vaccine_Name);

            Ext.getCmp('VacDopCh_VaccineName').setValue(this.formParams.Vaccine_Name);
            Ext.getCmp('VacDopCh_VaccineNick').setValue(this.formParams.Vaccine_Nick);
            
            this.formStore.load({
            params: {
                Vaccine_id: record.Vaccine_id
            },
            callback: function() {

                var formStoreCount = this.formStore.getCount() > 0;
                
                if (formStoreCount) {
                    
                    var formStoreRecord = this.formStore.getAt(0);
                    //this.formParams.OtherVacAgeBorders_id = formStoreRecord.get('OtherVacAgeBorders_id');
                    this.formParams.Vaccine_id = formStoreRecord.get('Vaccine_id');
                    this.formParams.AgeTypeS1 = formStoreRecord.get('AgeTypeS1');
                    this.formParams.AgeS1 = formStoreRecord.get('AgeS1');
                    this.formParams.AgeTypeS2 = formStoreRecord.get('AgeTypeS2');
                    this.formParams.AgeS2 = formStoreRecord.get('AgeS2');
                    this.formParams.AgeTypeE1 = formStoreRecord.get('AgeTypeE1');
                    this.formParams.AgeE1 = formStoreRecord.get('AgeE1');
                    this.formParams.AgeTypeE2 = formStoreRecord.get('AgeTypeE2');
                    this.formParams.AgeE2 = formStoreRecord.get('AgeE2')
                    this.formParams.PeriodTypeInterval1 = formStoreRecord.get('PeriodTypeInterval1');
                    this.formParams.Interval1 = formStoreRecord.get('Interval1');
                    this.formParams.PeriodTypeInterval2 = formStoreRecord.get('PeriodTypeInterval2');
                    this.formParams.Interval2 = formStoreRecord.get('Interval2');
                    this.formParams.PeriodTypeIntervalRisk1 = formStoreRecord.get('PeriodTypeIntervalRisk1');
                    this.formParams.IntervalRisk1 = formStoreRecord.get('IntervalRisk1');
                    this.formParams.PeriodTypeIntervalRisk2 = formStoreRecord.get('PeriodTypeIntervalRisk2');
                    this.formParams.IntervalRisk2 = formStoreRecord.get('IntervalRisk2');
                    this.formParams.multiplicity1 = formStoreRecord.get('multiplicity1');
                    this.formParams.multiplicity2 = formStoreRecord.get('multiplicity2');
                    this.formParams.multiplicityRisk1 = formStoreRecord.get('multiplicityRisk1');
                    this.formParams.multiplicityRisk2 = formStoreRecord.get('multiplicityRisk2');

                    sw.Promed.vac.utils.consoleLog('formParams');
                    sw.Promed.vac.utils.consoleLog(this.formParams);
                    
                    if (this.formParams.AgeTypeS1 == undefined) {  //  Не зависит от возраста пациента
                        Ext.getCmp('VacDopCh_AgeVac').setValue(false);
                    }
                    else {
                         Ext.getCmp('VacDopCh_AgeVac').setValue(true);
                         Ext.getCmp('VacDopCh_DopAreaRange11').setValue(this.formParams.AgeS1);
                         Ext.getCmp('VacDopCh_DopAreaRange12').setValue(this.formParams.AgeE1);
                         Ext.getCmp('VacDopCh_DopKol1').setValue(this.formParams.multiplicity1);
                         Ext.getCmp('VacDopCh_DopAreaRange21').setValue(this.formParams.AgeS2);
                         Ext.getCmp('VacDopCh_DopAreaRange22').setValue(this.formParams.AgeE2);
                         Ext.getCmp('VacDopCh_DopKol2').setValue(this.formParams.multiplicity2);
                         Ext.getCmp('VacDopCh_AgeTypePeriodCombo1').setValue(this.formParams.AgeTypeS1); 
                         Ext.getCmp('VacDopCh_AgeTypePeriodCombo2').setValue(this.formParams.AgeTypeS2); 
//                         if (this.formParams.multiplicityRisk1 != undefined) {
//                              Ext.getCmp('VacDopCh_DopKol1').setValue(this.formParams.multiplicity1)!!!
//                         }
                    };    
                    
                    //lert(this.formParams.AgeTypeS1);
                     if (this.formParams.multiplicity1 == undefined) {
                         
                     } else 
                         Ext.getCmp('VacDopCh_DopKol1').setValue(this.formParams.multiplicity1);
                         if (this.formParams.multiplicity1 == 1) {
                            Ext.getCmp('VacDopCh_DopKol1').setValue(this.formParams.multiplicity1); 
                            Ext.getCmp('VacDopCh_DopInterval1').setValue(0);
                            Ext.getCmp('VacDopCh_DopInterval1').setContainerVisible(false);
                            Ext.getCmp('VacDopCh_DopInterval1').allowBlank = true
                         } else {
                              Ext.getCmp('VacDopCh_DopInterval1').setValue(this.formParams.Interval1);
                              Ext.getCmp('VacDopCh_DopInterval1').setContainerVisible(true);
                            Ext.getCmp('VacDopCh_DopInterval1').allowBlank = false
                         }
                         if (this.formParams.multiplicity2 == 1) {
                             Ext.getCmp('VacDopCh_DopInterval2').setValue(0);
                             Ext.getCmp('VacDopCh_DopInterval2').setContainerVisible(false);
                             Ext.getCmp('VacDopCh_DopInterval2').allowBlank = true
                         } else {
                              Ext.getCmp('VacDopCh_DopInterval2').setValue(this.formParams.Interval2);
                              Ext.getCmp('VacDopCh_DopInterval2').setContainerVisible(true);
                              Ext.getCmp('VacDopCh_DopInterval2').allowBlank = false
                         }
                         
                         if (this.formParams.multiplicityRisk1 == undefined) {
                             Ext.getCmp('VacDopCh_Risk1').setValue(false);
                             Ext.getCmp('VacDopCh_DopKolRisk1').setValue(null);
                             Ext.getCmp('VacDopCh_DopIntervalRisk1').setValue(null);
                            //if  (Ext.getCmp('VacDopCh_DopKol1')  == undefined
                         }
                         else {
                             Ext.getCmp('VacDopCh_Risk1').setValue(true);
                             Ext.getCmp('VacDopCh_DopKolRisk1').setValue(this.formParams.multiplicityRisk1);
                             if (this.formParams.multiplicityRisk1 == 1) {
                                 Ext.getCmp('VacDopCh_DopIntervalRisk1').setValue(0);
                                 Ext.getCmp('VacDopCh_DopIntervalRisk1').setContainerVisible(false);
                                 Ext.getCmp('VacDopCh_DopIntervalRisk1').allowBlank = true
                             } else {
                                 Ext.getCmp('VacDopCh_DopIntervalRisk1').setValue(this.formParams.IntervalRisk1);
                                 Ext.getCmp('VacDopCh_DopIntervalRisk1').setContainerVisible(true);
                                 Ext.getCmp('VacDopCh_DopIntervalRisk1').allowBlank = false
                             }
                             
                         }
                         if (this.formParams.multiplicityRisk2 == undefined) {
                             Ext.getCmp('VacDopCh_Risk2').setValue(false);
                             Ext.getCmp('VacDopCh_DopKolRisk2').setValue(null);
                             Ext.getCmp('VacDopCh_DopIntervalRisk2').setValue(null);
                         }
                         else {
                             Ext.getCmp('VacDopCh_Risk2').setValue(true);
                             Ext.getCmp('VacDopCh_DopKolRisk2').setValue(this.formParams.multiplicityRisk2);
                             if (this.formParams.multiplicityRisk2 == 1) {
                                 Ext.getCmp('VacDopCh_DopIntervalRisk2').setValue(0);
                                 Ext.getCmp('VacDopCh_DopIntervalRisk2').setContainerVisible(false);
                                 Ext.getCmp('VacDopCh_DopIntervalRisk2').allowBlank = true
                             } else {
                                 Ext.getCmp('VacDopCh_DopIntervalRisk2').setValue(this.formParams.IntervalRisk2);
                                 Ext.getCmp('VacDopCh_DopIntervalRisk2').setContainerVisible(true);
                                 Ext.getCmp('VacDopCh_DopIntervalRisk2').allowBlank = false
                             }
                             //Ext.getCmp('VacDopCh_DopIntervalRisk2').setValue(this.formParams.IntervalRisk2);
                         }
//                         Ext.getCmp('VacDopCh_DopKol2').setValue(this.formParams.multiplicity2);
//                         Ext.getCmp('VacDopCh_DopKolRisk2').setValue(this.formParams.multiplicityRisk2);
                         
                         
                         // (checkbox, checked)
//                    };
                    
                    

                } else
                    Ext.getCmp('VacDopCh_AgeVac').setValue(false);
                Ext.getCmp('VacDopCh_AgeVac').fireEvent('check', null, Ext.getCmp('VacDopCh_AgeVac').getValue());
            }.createDelegate(this)
            })
           
        }

    }
})

