/**
 * swElectronicInfomatLinkEditWindow - окно редактирования назначения очередей на инфомат
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
sw.Promed.swElectronicInfomatLinkEditWindow = Ext.extend(sw.Promed.BaseForm, {

    id: 'ElectronicInfomatLinkEditWindow',
    autoHeight: false,
    layout: 'form',
    modal: true,
    resizable: false,
    width: 680,
    height: 150,
    // имя основной формы
    formName: 'ElectronicInfomatLinkEditForm',
    // краткое имя формы (для айдишников)
    formPrefix: 'EILEW_',

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
            combo = form.findField('ElectronicQueueInfo_id'),
            eqi_data = combo.getSelectedRecordData();

        params.ElectronicQueueInfo_Code = !Ext.isEmpty(eqi_data.ElectronicQueueInfo_Code) ? eqi_data.ElectronicQueueInfo_Code : null;
        params.ElectronicQueueInfo_Name = !Ext.isEmpty(eqi_data.ElectronicQueueInfo_Name) ? eqi_data.ElectronicQueueInfo_Name : null;

        wnd.onSave(params);
        wnd.hide();

        return true;
    },

    show: function() {

        sw.Promed.swElectronicInfomatLinkEditWindow.superclass.show.apply(this, arguments);

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
            log(field_name +':'+ args[field_name]);
            wnd[field_name] = args[field_name];
        }

        form.setValues(args);
        loadMask.show();

        var combo = form.findField('ElectronicQueueInfo_id');

        combo.getStore().baseParams.Lpu_id = wnd.Lpu_id;
        combo.getStore().baseParams.LpuBuilding_id = wnd.LpuBuilding_id ? wnd.LpuBuilding_id : null;

        combo.getStore().load();

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
            url:'/?c=ElectronicInfomat&m=saveElectronicInfomatLink',
            labelWidth: 120,
            labelAlign: 'right',

            items: [
                {
                    xtype: 'hidden',
                    name: 'ElectronicInfomatLink_id'
                },
                {
                    xtype: 'hidden',
                    name: 'Lpu_id'
                },
                {
                    xtype: 'swcustomownercombo',
                    fieldLabel: 'Наименование',
                    hiddenName: 'ElectronicQueueInfo_id',
                    displayField: 'ElectronicQueueInfo_Name',
                    valueField: 'ElectronicQueueInfo_id',
                    width: 475,
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
                        url:'/?c=ElectronicInfomat&m=loadElectronicQueueInfoCombo'
                    }),
                    ownerWindow: wnd,
                    tpl: new Ext.XTemplate(
                        '<tpl for="."><div class="x-combo-list-item">',
                        '<font color="red">{ElectronicQueueInfo_Code}</font>&nbsp;{ElectronicQueueInfo_Name}',
                        '</div></tpl>'
                    ),
                },
            ]
        });

        Ext.apply(this, {
            buttons:
                [{
                    handler: function()
                    {
                        this.ownerCt.doSave();
                    },
                    iconCls: 'save16',
                    text: BTN_FRMADD
                },
                    {
                        text: '-'
                    },
                    HelpButton(this, 0),
                    {
                        handler: function()
                        {
                            this.ownerCt.hide();
                        },
                        iconCls: 'cancel16',
                        text: BTN_FRMCANCEL
                    }],
            items: [
                this[this.formName]
            ]
        });

        sw.Promed.swElectronicInfomatLinkEditWindow.superclass.initComponent.apply(this, arguments);
    }
});