/**
 * swElectronicTalonRedirectEditWindow - окно перенаправления талона
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Ambulance
 * @access       public
 * @copyright    Copyright (c) 2017 Swan Ltd.
 * @author       Sysolin Maksim
 * @version      11.2017
 * @comment
 */
sw.Promed.swElectronicTalonRedirectEditWindow = Ext.extend(sw.Promed.BaseForm, {

    id: 'ElectronicTalonRedirectEditWindow',
    autoHeight: false,
    layout: 'form',
    modal: true,
    resizable: false,
    width: 530,
    height: 150,
    // имя основной формы
    formName: 'ElectronicTalonRedirectEditForm',
    // краткое имя формы (для айдишников)
    formPrefix: 'ETREW_',

    getMainForm: function(){ return this[this.formName].getForm(); },
    setDisabled: function(disable) {

        var wnd = this,
            form = wnd.getMainForm(),
            field_arr = [];

        form.items.each(function(field){
            field.setDisabled(disable);
        });

        if (disable) {
            wnd.buttons[0].disable();
            wnd.buttons[1].disable();
        } else {
            wnd.buttons[0].enable();
            wnd.buttons[1].enable();
        }
    },
    completeFn: function(){

        var wnd = this;

        if (this.panelCompleteFn && typeof this.panelCompleteFn === 'function') {

            this.panelCompleteFn({
                bypassCheckRedirection: true,
                callback: function() {
                    wnd.hide();
                }
            });

        } else { log('no redirect completeFn defined');}
    },
    getPrimaryElectronicService: function(params){

        if ( typeof params != 'object' ) { params = new Object() }

        var wnd = this;
        Ext.Ajax.request({
            url: '/?c=ElectronicTalon&m=getPrimaryElectronicService',
            params: {ElectronicTalon_id:wnd.ElectronicTalon_id},
            callback: function (opt, success, response) {
                if (success) {
                    if ( response.responseText.length > 0 ) {

                        var result = Ext.util.JSON.decode(response.responseText);

                        if (result && result[0] && result[0].ElectronicService_id) {

                            wnd.PrimaryElectronicService_id = result[0].ElectronicService_id;

                        } else {  wnd.getLoadMask().hide(); }
                    }
                }

                if (params.callback && typeof params.callback === 'function') { params.callback(); }
            }
        });
    },
    submit: function()
    {
        var wnd = this,
            form = wnd.getMainForm();

        if (!form.isValid()) {

            sw.swMsg.show({

                msg: ERR_INVFIELDS_MSG,
                title: ERR_INVFIELDS_TIT,
                icon: Ext.Msg.WARNING,
                buttons: Ext.Msg.OK,

                fn: function() {
                    wnd[wnd.formName].getFirstInvalidEl().focus(true);
                }
            });

            return false;
        }



        var params = form.getValues();

        //log('p',params); return false;
        var selectedRecord = form.findField('ElectronicService_id').getSelectedRecordData();

        if (selectedRecord) {
            params.MedStaffFact_id = selectedRecord.MedStaffFact_id;
            params.UslugaComplexMedService_id = selectedRecord.UslugaComplexMedService_id;
            params.MedServiceType_SysNick = selectedRecord.MedServiceType_SysNick;
            params.MedService_id = selectedRecord.MedService_id;
        }

        log('selectedRecord',selectedRecord);

        // если перенаправление в службу
        if (params.MedServiceType_SysNick && params.MedServiceType_SysNick != 'regpol') {

            // открываем ЭМК со случаем лечения, где выбираем назначение на ФД
            if (params.MedServiceType_SysNick === 'func' || params.MedServiceType_SysNick === 'pzm') {

                sw.swMsg.show({

                    msg: "Для перенаправления в данный пункт обслуживания необходимо создать назначение с типом " +
                        (params.MedServiceType_SysNick === 'pzm' ? "Лабораторная" : "Инструментальная" ) +
                        " диагностика из случая лечения.",
                    title: ERR_INVFIELDS_TIT,
                    icon: Ext.Msg.WARNING,
                    buttons: Ext.Msg.OKCANCEL,
                    fn: function ( buttonId ) {
                        if ( buttonId === 'ok' ) {
                            if (wnd.showEvnPL && typeof wnd.showEvnPL === 'function') {
                                wnd.hide();
                                wnd.showEvnPL(params);
                            }
                        }
                    }
                });


            } else {
                sw.swMsg.show({

                    msg: "Перенаправления в данную службу не поддерживаются",
                    title: ERR_INVFIELDS_TIT,
                    icon: Ext.Msg.WARNING,
                    buttons: Ext.Msg.OK,

                    fn: function() {}
                });

                return false;
            }

        } else {
        wnd.getLoadMask('Перенаправление...').show();
        form.submit({
                params: params,
                success: function(form, action) {
                wnd.getLoadMask().hide();
                if (action.result) {
                    wnd.hide();
                        if (wnd.callback && typeof wnd.callback === 'function') {
                            wnd.callback();
                        }
                    }
                }
        });
        }

    },

    show: function() {

        sw.Promed['sw'+this.id].superclass.show.apply(this, arguments);

        var wnd = this,
            form = wnd.getMainForm(),
            loadMask = new Ext.LoadMask(
                wnd.getEl(),{
                    msg: LOAD_WAIT
                }
            );

        wnd.action = null;

        if (!arguments[0]){

            sw.swMsg.show({

                buttons: Ext.Msg.OK,
                icon: Ext.Msg.ERROR,
                title: lang['oshibka'],
                msg: lang['oshibka_otkryitiya_formyi_ne_ukazanyi_nujnyie_vhodnyie_parametryi'],

                fn: function() { wnd.hide(); }
            });
        }

        var args = arguments[0];
        wnd.focus();
        form.reset();

        this.setTitle("Перенаправление талона");

        for (var field_name in args) {
            log(field_name +':'+ args[field_name]);
            wnd[field_name] = args[field_name];
        }

        form.setValues(args);
        loadMask.show();

        wnd.getPrimaryElectronicService({
            callback: function(){

                var combo = form.findField('ElectronicService_id'),
                    comboStore = combo.getStore(),
                    lpu_id = form.findField('Lpu_id').getValue(),
                    lpubuilding_id = form.findField('LpuBuilding_id').getValue(),
                    current_electronicservice_id = form.findField('current_ElectronicService_id').getValue(),
                    fromElectronicService_id = form.findField('fromElectronicService_id').getValue(),
                    redirectBack = form.findField('redirectBack').getValue(),
                    ElectronicTalon_id = form.findField('ElectronicTalon_id').getValue();


                combo.addListener('clearCombo', function(){
                    var infoLabel = Ext.getCmp(wnd.id+'_RedirectInfo');
                    infoLabel.setText('');
                });

                if (redirectBack) wnd.buttons[0].show(); // кнопка завершения приема


                comboStore.baseParams.Lpu_id = lpu_id;
                comboStore.baseParams.LpuBuilding_id = lpubuilding_id;
                comboStore.baseParams.CurrentElectronicService_id = current_electronicservice_id;
                comboStore.baseParams.ElectronicTalon_id = ElectronicTalon_id;

                comboStore.load({
                    params: {
                        Lpu_id: lpu_id,
                        LpuBuilding_id: lpubuilding_id,
                        CurrentElectronicService_id: current_electronicservice_id,
                        ElectronicTalon_id: ElectronicTalon_id
                    },
                    callback: function(){
                        combo.setValue(fromElectronicService_id);
                        var recordIndex = comboStore.find('ElectronicService_id', fromElectronicService_id);
                        if (recordIndex != -1) {
                            var record = comboStore.getAt(recordIndex);
                            combo.fireEvent('select', combo, record);
                        }
                    }
                });

                loadMask.hide();

                switch (wnd.action) {
                    case 'add':
                    case 'edit':
                    case 'view':

                        wnd.setDisabled(false);
                        break;
                }
            }
        });
    },
    initComponent: function() {

        var wnd = this,
            formName = wnd.formName,
            formPrefix = wnd.formPrefix;

        wnd[formName] = new Ext.form.FormPanel({

            bodyStyle: '{padding-top: 15px;}',
            border: false,
            bodyBorder: false,
            height: 350,
            frame: true,
            layout: 'form',
            id: formName,
            url:'/?c=ElectronicTalon&m=redirectElectronicTalon',
            labelWidth: 140,
            labelAlign: 'right',

            items: [
                {
                    xtype: 'hidden',
                    name: 'ElectronicTalon_id'
                },
                {
                    xtype: 'hidden',
                    name: 'Lpu_id'
                },
                {
                    xtype: 'hidden',
                    name: 'LpuBuilding_id'
                },
                {
                    xtype: 'hidden',
                    name: 'pmUser_id'
                },
                {
                    xtype: 'hidden',
                    name: 'EvnDirection_pid'
                },
                {
                    xtype: 'hidden',
                    name: 'LpuSectionProfile_id'
                },
                {
                    xtype: 'hidden',
                    name: 'LpuSection_id'
                },
                {
                    xtype: 'hidden',
                    name: 'From_MedStaffFact_id'
                },
                {
                    xtype: 'hidden',
                    name: 'fromElectronicService_id'
                },
                {
                    xtype: 'hidden',
                    name: 'MedPersonal_id'
                },
                {
                    xtype: 'hidden',
                    name: 'current_ElectronicService_id'
                },
                {
                    xtype: 'hidden',
                    name: 'redirectBack'
                },
                {
                    xtype: 'swcustomownercombo',
                    fieldLabel: 'Пункт обслуживания',
                    hiddenName: 'ElectronicService_id',
                    displayField: 'ElectronicService_Name',
                    valueField: 'ElectronicService_id',
                    width: 330,
                    allowBlank: false,
                    store: new Ext.data.SimpleStore({
                        autoLoad: false,
                        fields: [
                            { name: 'ElectronicService_id', mapping: 'ElectronicService_id' },
                            { name: 'ElectronicService_Name', mapping: 'ElectronicService_Name' },
                            { name: 'ElectronicService_Code', mapping: 'ElectronicService_Code' },
                            { name: 'ElectronicQueueInfo_Name', mapping: 'ElectronicQueueInfo_Name'},
                            { name: 'ElectronicService_Load', mapping: 'ElectronicService_Load' },
                            { name: 'MedStaffFact_id', mapping: 'MedStaffFact_id' },
                            { name: 'LpuBuilding_Name', mapping: 'LpuBuilding_Name' },
                            { name: 'UslugaComplexMedService_id', mapping: 'UslugaComplexMedService_id'},
                            { name: 'MedServiceType_SysNick', mapping: 'MedServiceType_SysNick' },
                            { name: 'MedService_id', mapping: 'MedService_id' },
                            { name: 'wasRedirectedTo', mapping: 'wasRedirectedTo' },
                            { name: 'GroupName', mapping: 'GroupName' },
                            { name: 'isPrimaryElectronicService', mapping: 'isPrimaryElectronicService' }

                        ],
                        remoteSort: true,
                        key: 'ElectronicService_id',
                        url:'/?c=ElectronicTalon&m=loadLpuBuildingElectronicServices'
                    }),
                    ownerWindow: wnd,
                    tpl: new Ext.XTemplate(
                        '<tpl for="."><div class="x-combo-list-item">',
                        '{[ values.GroupName ? "<span style=\'font-size: 15px; font-weight: bold;\'>" + values.GroupName + "</span>" : "<div style=\'padding-left: 20px;\'><span style=\'color: red\'> к. " + values.ElectronicService_Code + "</span>&nbsp;<span style=\'font-weight: bold;\'>" + values.ElectronicQueueInfo_Name+ "</span>&nbsp;"+ values.ElectronicService_Name+ "<p>[Ожидают приема: " + values.ElectronicService_Load + "]</p></div>" ]}',
                        '</div></tpl>'
                    ),
                    listeners: {
                        'select': function(el, record) {
                            if (record) el.fireEvent('change', el, record.get('ElectronicService_id'));
                        },
                        'change': function(el, selectedElectronicService_id) {

                            var infoLabel = Ext.getCmp(wnd.id+'_RedirectInfo');
                            infoLabel.setText('');

                            var fromElectronicService_id = wnd.getMainForm().findField('fromElectronicService_id').getValue();
                            var redirectBack = wnd.getMainForm().findField('redirectBack');

                            var selectedComboRecord = el.getSelectedRecordData();

                            if (selectedComboRecord
                                && selectedComboRecord.wasRedirectedTo != undefined
                                && selectedComboRecord.wasRedirectedTo)
                            {
                                var txt = '';

                                // если сегодня в выбранный ПО уже перенаправлялся этот талон,
                                // ставим флаг возврата и меняем надпись кнопки
                                if (fromElectronicService_id == selectedElectronicService_id) {
                                    txt += '<span style="font-weight:bold;">Талон был перенаправлен из этого ПО</span>';
                                }

                                if (wnd.PrimaryElectronicService_id == selectedElectronicService_id) {
                                    txt += '<br>Это первоначальный пункт обслуживания';
                                }

                                if (txt == '') { txt = 'Талон проходил сегодня данный пункт обслуживания'}

                                infoLabel.setText(txt, false);
                                redirectBack.setValue(true);

                            } else {
                                redirectBack.setValue(false);
                            }
                        }
                    }
                    },
                {
                    xtype: 'label',
                    id: wnd.id+'_RedirectInfo',
                    style: '{' +
                    'display: inline-block;' +
                    ' margin-left: 150px;' +
                    'text-align: left;' +
                    ' margin-bottom: 20px;' +
                    '}',
                    html: ''
                }
            ]
        });

        Ext.apply(this, {
            buttons:
                [
                    {
                        handler: function() { this.ownerCt.completeFn(); },
                        iconCls: 'chair16',
                        text: '&nbspЗавершить прием',
                        hidden: true
                        },
                    {
                        handler: function() { this.ownerCt.hide(); },
                        iconCls: 'cancel16',
                        text: 'Отмена'
                    },
                    {text: '-'},
                    {
                        handler: function() { this.ownerCt.submit(); },
                        iconCls: 'ok16',
                        text: '&nbspОк'
                    }
                ],
            items: [this[this.formName]]
        });

        sw.Promed['sw'+this.id].superclass.initComponent.apply(this, arguments);
    }
});