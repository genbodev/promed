/**
 * swElectronicScoreboardQueueLinkEditWindow - окно редактирования назначения очередей на табло
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Ambulance
 * @access       public
 * @copyright    Copyright (c) 2017 Swan Ltd.
 * @author       Sysolin Maksim
 * @version      08.2017
 * @comment
 */
sw.Promed.swElectronicScoreboardQueueLinkEditWindow = Ext.extend(sw.Promed.BaseForm, {

    id: 'ElectronicScoreboardQueueLinkEditWindow',
    autoHeight: false,
    layout: 'form',
    modal: true,
    resizable: false,
    width: 680,
    height: 150,
    // имя основной формы
    formName: 'ElectronicScoreboardQueueLinkEditForm',
    // краткое имя формы (для айдишников)
    formPrefix: 'ESQLEW_',

    getMainForm: function()
    {
        return this[this.formName].getForm();
    },

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

    doSave:  function() {

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

        var params = form.getValues(),
            electronicQueueCombo = form.findField('ElectronicQueueInfo_id'),
            electronicServiceCombo = form.findField('ElectronicService_id'),
            eqiMetadata = electronicQueueCombo.getSelectedRecordData(),
            esMetadata = electronicServiceCombo.getSelectedRecordData();

        // если есть объект со списком ЭО из основного грида
        if (wnd.existedQueueIdList && typeof wnd.existedQueueIdList == 'object') {

            var newQueueValue = electronicQueueCombo.getValue();
            var newServiceValue = electronicServiceCombo.getValue();

            var validated = true;

            // перебираем наш объект с ЭО
            Object.keys(wnd.existedQueueIdList).forEach(function(existedQueueId) {

                var servicesList = wnd.existedQueueIdList[existedQueueId];
                log('electronicQueueInfo_id: ' + existedQueueId + ', services: ', servicesList)

                // если массив объекта с ЭО заполнен ПО
                if (servicesList.length > 0) {

                    // проверяем его по дублям ЭО, если не указан ПО
                    if (!newServiceValue && newQueueValue == existedQueueId) {

                        sw.swMsg.show({

                            msg: 'Нельзя назначить электронную очередь полностью, пока один из её пунктов обслуживания связан с этим табло',
                            title: ERR_INVFIELDS_TIT,
                            icon: Ext.Msg.WARNING,
                            buttons: Ext.Msg.OK,

                            fn: function() { electronicQueueCombo.focus(true) }
                        });

                        validated = false;

                    // если ПО указан, сверяем ПО
                    } else {

                        if (newServiceValue.inlist(servicesList)) {

                            sw.swMsg.show({

                                msg: 'На одно электронное табло нельзя назначить одну и ту же электронную очередь с одним и тем же пунктом обслуживания несколько раз',
                                title: ERR_INVFIELDS_TIT,
                                icon: Ext.Msg.WARNING,
                                buttons: Ext.Msg.OK,

                                fn: function() { electronicQueueCombo.focus(true) }
                            });

                            validated = false;
                        }
                    }

                } else {

                    var errMsg = (newServiceValue)
                        ? 'Электронное табло содержит электронную очередь в которую уже включен указанный пункт обслуживания'
                        : 'На одно электронное табло нельзя назначить одну и ту же электронную очередь несколько раз';

                    // проверяем его по дублям ЭО
                    if (newQueueValue == existedQueueId) {

                        sw.swMsg.show({

                            msg: errMsg,
                            title: ERR_INVFIELDS_TIT,
                            icon: Ext.Msg.WARNING,
                            buttons: Ext.Msg.OK,

                            fn: function() { electronicQueueCombo.focus(true) }
                        });

                        validated = false;
                    }
                }
            });

            if (!validated) return false;
        }

        params.ElectronicQueueInfo_Code = !Ext.isEmpty(eqiMetadata.ElectronicQueueInfo_Code) ? eqiMetadata.ElectronicQueueInfo_Code : null;
        params.ElectronicQueueInfo_Name = !Ext.isEmpty(eqiMetadata.ElectronicQueueInfo_Name) ? eqiMetadata.ElectronicQueueInfo_Name : null;

        // если тип табло - светодиодное
        if (wnd.showElectronicServiceCombo) params.ElectronicService_Name = !Ext.isEmpty(esMetadata.ElectronicService_Name) ? esMetadata.ElectronicService_Name : null;

        wnd.onSave(params);
        wnd.hide();

        return true;
    },

    show: function() {

        sw.Promed.swElectronicScoreboardQueueLinkEditWindow.superclass.show.apply(this, arguments);

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

        this.setTitle("Назначение электронной очереди");

        for (var field_name in args) {
            log(field_name +': '+ args[field_name]);
            wnd[field_name] = args[field_name];
        }

        Object.keys(wnd.existedQueueIdList).forEach(function(obj){
            log('electronicQueueInfo_id: ' + obj + ', services: ', wnd.existedQueueIdList[obj])
        });

        form.setValues(args);
        loadMask.show();

        var electronicQueueCombo = form.findField('ElectronicQueueInfo_id'),
            electronicServiceCombo = form.findField('ElectronicService_id'),
            Lpu_id = form.findField('Lpu_id').getValue();

        // если тип табло ТЕЛЕВИЗОР
        if (!wnd.showElectronicServiceCombo) electronicServiceCombo.hideContainer();
        else electronicServiceCombo.showContainer();

        electronicQueueCombo.getStore().baseParams.Lpu_id = Lpu_id;
        electronicQueueCombo.getStore().baseParams.LpuBuilding_id = wnd.LpuBuilding_id ? wnd.LpuBuilding_id : null;

        electronicQueueCombo.getStore().load();

        switch (wnd.action) {
            case 'add':

                wnd.setTitle(this.title + ": Добавление");
                loadMask.hide();
                wnd.setDisabled(false);
                break;

            case 'edit':
            case 'view':
                break;
        }
    },
    initComponent: function() {

        var wnd = this,
            formName = wnd.formName,
            formPrefix = wnd.formPrefix;

        wnd[formName] = new Ext.form.FormPanel({

            bodyStyle: '{padding-top: 15px;}',
            border: false,
            bodyBorder: false,
            height: 150,
            frame: true,
            layout: 'form',
            id: formName,
            url:'/?c=ElectronicScoreboard&m=saveElectronicScoreboardQueueLink',
            labelWidth: 180,
            labelAlign: 'right',

            items: [
                {
                    xtype: 'hidden',
                    name: 'ElectronicScoreboardQueueLink_id'
                },
                {
                    xtype: 'hidden',
                    name: 'Lpu_id'
                },
                {
                    xtype: 'swcustomownercombo',
                    fieldLabel: 'Электронная очередь',
                    hiddenName: 'ElectronicQueueInfo_id',
                    displayField: 'ElectronicQueueInfo_Name',
                    valueField: 'ElectronicQueueInfo_id',
                    width: 430,
                    allowBlank: false,
                    store: new Ext.data.SimpleStore({
                        autoLoad: false,
                        fields: [
                            { name: 'ElectronicQueueInfo_id', mapping: 'ElectronicQueueInfo_id' },
                            { name: 'ElectronicQueueInfo_Name', mapping: 'ElectronicQueueInfo_Name' },
                            { name: 'ElectronicQueueInfo_Code', mapping: 'ElectronicQueueInfo_Code' },
                        ],
                        key: 'ElectronicQueueInfo_id',
                        sortInfo: { field: 'ElectronicQueueInfo_Name' },
                        url:'/?c=ElectronicScoreboard&m=loadElectronicQueueInfoCombo'
                    }),

                    ownerWindow: wnd,
                    tpl: new Ext.XTemplate(
                        '<tpl for="."><div class="x-combo-list-item">',
                        '<font color="red">{ElectronicQueueInfo_Code}</font>&nbsp;{ElectronicQueueInfo_Name}',
                        '</div></tpl>'
                    ),
                    listeners: {
                        change: function(combo, val, newVal){

                            var childCombo = wnd.getMainForm().findField('ElectronicService_id');

                            if (wnd.showElectronicServiceCombo) {

                                childCombo.clearValue();
                                childCombo.getStore().baseParams.ElectronicQueueInfo_id = val;
                                childCombo.getStore().load();
                            }
                        }
                    }
                },
                {
                    xtype: 'swcustomownercombo',
                    fieldLabel: 'Пункт обслуживания',
                    hiddenName: 'ElectronicService_id',
                    displayField: 'ElectronicService_Name',
                    valueField: 'ElectronicService_id',
                    width: 430,
                    allowBlank: true,
                    store: new Ext.data.SimpleStore({
                        autoLoad: false,
                        fields: [
                            { name: 'ElectronicService_id', mapping: 'ElectronicService_id' },
                            { name: 'ElectronicService_Name', mapping: 'ElectronicService_Name' },
                            { name: 'ElectronicService_Nick', mapping: 'ElectronicService_Nick' },
                            { name: 'ElectronicService_Code', mapping: 'ElectronicService_Code' },
                        ],
                        key: 'ElectronicService_id',
                        sortInfo: { field: 'ElectronicService_Code' },
                        url:'/?c=ElectronicScoreboard&m=loadElectronicServiceCombo'
                    }),

                    ownerWindow: wnd,
                    tpl: new Ext.XTemplate(
                        '<tpl for="."><div class="x-combo-list-item">',
                        '<font color="red">{ElectronicService_Nick}</font>&nbsp;{ElectronicService_Name}',
                        '</div></tpl>'
                    ),
                },
            ]
        });

        Ext.apply(this, {
            buttons:
                [   {
                        handler: function() { this.ownerCt.doSave(); },
                        iconCls: 'save16',
                        text: BTN_FRMADD
                    },
                    { text: '-' }, HelpButton(this, 0),
                    {
                        handler: function() { this.ownerCt.hide(); },
                        iconCls: 'cancel16',
                        text: BTN_FRMCANCEL
                    }
                ],
            items: [
                this[this.formName]
            ]
        });

        sw.Promed.swElectronicScoreboardQueueLinkEditWindow.superclass.initComponent.apply(this, arguments);
    }
});