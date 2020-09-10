/**
 * swElectronicInfomatTreatmentLinkEditWindow - форма добавление инфомата к поводу
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Ambulance
 * @access       public
 * @copyright    Copyright (c) 2017 Swan Ltd.
 * @author       Sysolin Maksim
 * @version      04.2018
 * @comment
 */
sw.Promed.swElectronicInfomatTreatmentLinkEditWindow = Ext.extend(sw.Promed.BaseForm, {

    id: 'ElectronicInfomatTreatmentLinkEditWindow',
    autoHeight: false,
    layout: 'form',
    modal: true,
    resizable: false,
    width: 630,
    height: 150,
    // имя основной формы
    formName: 'ElectronicInfomatTreatmentLinkEditForm',
    // краткое имя формы (для айдишников)
    formPrefix: 'EITLEW_',

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

        wnd.getLoadMask('Сохранение...').show();
        form.submit({
            success: function(form, action) {
                wnd.getLoadMask().hide();
                if (action.result) {
                    wnd.hide();
                    if (wnd.callback && typeof wnd.callback  === 'function') {
                        wnd.callback();
                    }
                }
            }
        });
    },

    show: function() {

        sw.Promed['sw'+this.id].superclass.show.apply(this, arguments);

        var wnd = this,
            form = wnd.getMainForm(),
            loadMask = new Ext.LoadMask(wnd.getEl(),{msg: LOAD_WAIT});

        wnd.action = null;

        if (!arguments[0]){

            sw.swMsg.show({

                buttons: Ext.Msg.OK,
                icon: Ext.Msg.ERROR,
                title: langs('Ошибка'),
                msg: langs('Ошибка открытия формы.<br/>Не указаны нужные входные параметры.'),

                fn: function() { wnd.hide(); }
            });
        }

        var args = arguments[0];
        wnd.focus();
        form.reset();

        this.setTitle("Добавление инфомата к группе поводов");

        for (var field_name in args) {
            log(field_name +':'+ args[field_name]);
            wnd[field_name] = args[field_name];
        }

        form.setValues(args);
        loadMask.show();

        var combo = form.findField('ElectronicInfomat_id'),
            lpu_id = form.findField('Lpu_id').getValue();

        combo.getStore().baseParams.Lpu_id = lpu_id;
        combo.getStore().load({ params: { Lpu_id: lpu_id }});

        loadMask.hide();

        switch (wnd.action) {
            case 'add':
            case 'edit':
            case 'view':

                wnd.setDisabled(false);
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
            height: 350,
            frame: true,
            layout: 'form',
            id: formName,
            url:'/?c=ElectronicTreatment&m=addElectronicInfomatTreatmentLink',
            labelWidth: 140,
            labelAlign: 'right',

            items: [
                {
                    xtype: 'hidden',
                    name: 'ElectronicInfomatTreatmentLink_id'
                },
                {
                    xtype: 'hidden',
                    name: 'Lpu_id'
                },
                {
                    xtype: 'hidden',
                    name: 'ElectronicTreatment_id'
                },
                {
                    xtype: 'swcustomownercombo',
                    fieldLabel: 'Инфомат',
                    hiddenName: 'ElectronicInfomat_id',
                    displayField: 'ElectronicInfomat_Name',
                    valueField: 'ElectronicInfomat_id',
                    width: 430,
                    allowBlank: false,
                    store: new Ext.data.SimpleStore({
                        autoLoad: false,
                        fields: [
                            { name: 'ElectronicInfomat_id', mapping: 'ElectronicInfomat_id' },
                            { name: 'ElectronicInfomat_Name', mapping: 'ElectronicInfomat_Name' },
                            { name: 'LpuBuilding_Name', mapping: 'LpuBuilding_Name' },
                            { name: 'LpuBuilding_Address', mapping: 'LpuBuilding_Address'}
                        ],
                        key: 'ElectronicInfomat_id',
                        sortInfo: { field: 'ElectronicInfomat_Name' },
                        url:'/?c=ElectronicInfomat&m=loadElectronicInfomatCombo'
                    }),
                    ownerWindow: wnd,
                    tpl: new Ext.XTemplate(
                        '<tpl for="."><div class="x-combo-list-item">',
                        '<font color="red">{ElectronicInfomat_id}</font>&nbsp;<span style="font-weight: bold">{ElectronicInfomat_Name}</span>',
                        '<p>[Подразделение: {LpuBuilding_Name} ]</p>',
                        '<p>[Адрес: {LpuBuilding_Address} ]</p>',
                        '</div></tpl>'
                    )
                }
            ]
        });

        Ext.apply(this, {
            buttons:
                [
                    {
                        handler: function() { this.ownerCt.submit(); },
                        iconCls: 'save16',
                        text: 'Добавить'
                    },
                    {text: '-'},
                    {
                        handler: function() { this.ownerCt.hide(); },
                        iconCls: 'cancel16',
                        text: 'Отмена'
                    }],
            items: [this[this.formName]]
        });

        sw.Promed['sw'+this.id].superclass.initComponent.apply(this, arguments);
    }
});