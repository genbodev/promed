/**
 * swWhsDocumentUcInventDrugInventoryEditWindow - окно формы назначения добавления\изменения номера описи
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Admin
 * @access			public
 * @copyright		Copyright (c) 2017 Swan Ltd.
 * @author			Max Sysolin (max.sysolin@gmail.com)
 * @version			13.06.2017
 */

sw.Promed.swWhsDocumentUcInventDrugInventoryEditWindow = Ext.extend(sw.Promed.BaseForm, {

    id: 'WhsDocumentUcInventDrugInventoryEditWindow',
    action: null,
    callback: Ext.emptyFn,
    closable: true,
    closeAction: 'hide',
    draggable: true,
    split: true,
    autoHeight: true,
    width: 320,
    layout: 'form',
    modal: true,
    plain: true,
    resizable: false,

    // дальше переменные только внутри этого окна
    // имя основной формы
    formName: 'WhsDocumentUcInventDrugInventoryEditForm',
    // краткое имя формы (для айдишников)
    formPrefix: 'WDUDIEW_',

    getMainForm: function()
    {
        return this[this.formName].getForm();
    },

    getFormField: function(name)
    {
        return this[this.formName].getForm().findField(name);
    },

    getFormFieldValue: function(name)
    {
        var form = this.getMainForm();
        return form.findField(name).value;
    },

    setFormFieldValue: function(name,val)
    {
        var form = this.getMainForm();
        return form.findField(name).setValue(val);
    },

    pushCallback: function() {

        var wnd = this;

        if (wnd.callback && typeof wnd.callback == 'function' ) {
            wnd.callback();
        }
    },

    doSave: function() {

        var wnd = this,
            form = wnd.getMainForm(),
            loadMask = new Ext.LoadMask(

                wnd.getEl(), {
                    msg: lang['podojdite_idet_sohranenie']
                }
            );

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

        loadMask.show();

        form.submit({
            params: {
                //WhsDocumentUcInventDrug_List : wnd.WhsDocumentUcInventDrug_List.length > 0 ? wnd.WhsDocumentUcInventDrug_List.join(',') : null
            },
            failure: function(result_form, action) {

                loadMask.hide();

                if (action.result)
                    if (action.result.Error_Code)

                        Ext.Msg.alert(

                            lang['oshibka_#'] + action.result.Error_Code,
                            action.result.Error_Message
                        );
            },
            success: function(result_form, action) {

                loadMask.hide();

                if (action.result) {
                    wnd.pushCallback();
                    wnd.hide();
                } else
                    sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki']);
            }
        });
    },

    show: function() {

        sw.Promed.swWhsDocumentUcInventDrugInventoryEditWindow.superclass.show.apply(this, arguments);

        var wnd = this,
            form = wnd.getMainForm(),

            loadMask = new Ext.LoadMask(
                wnd.getEl(),{
                    msg: LOAD_WAIT
                }
            );

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
        form.setValues(args);

        this.setTitle('Инвентаризационная опись');

        // оптимизировал присвоение параметров arguments[0]
        for (var field_name in args) {
            log(field_name +':'+ args[field_name]);
            wnd[field_name] = args[field_name];
        }

        form.clearInvalid();
        loadMask.show();

        switch (args.action) {

            case 'add':
                this.setTitle(this.title + ": Добавление");
                loadMask.hide();
                break;

            case 'edit':
                this.setTitle(this.title + ": Редактирование");

                // ради одного параметра такое конечно делать затратно, зато безопасно
                Ext.Ajax.request({
                    failure:function () {
                        sw.swMsg.alert(lang['oshibka'], lang['ne_udalos_poluchit_dannyie_s_servera']);
                        loadMask.hide();
                        wnd.hide();
                    },
                    params:{
                        WhsDocumentUcInventDrugInventory_id: wnd.WhsDocumentUcInventDrugInventory_id
                    },
                    success: function (response) {
                        var response_obj = Ext.util.JSON.decode(response.responseText);
                        wnd.setFormFieldValue('WhsDocumentUcInventDrugInventory_InvNum',response_obj.WhsDocumentUcInventDrugInventory_InvNum);
                        loadMask.hide();
                    },
                    url:'/?c=WhsDocumentUcInvent&m=loadWhsDocumentUcInventDrugInventoryNum'
                });
                break;
        }
    },

    sendAjaxRequestPromise: function(ajax_params, url)
    {
        return new Promise(function(resolve, reject) {

            Ext.Ajax.request({

                params: ajax_params,
                url: url,
                success: function(response) {
                    resolve(JSON.parse(response.responseText))
                },
                failure: function(response) {reject(response)}
            })
        })
    },

    generateWhsDocumentUcInventNum: function()
    {
        var wnd = this,
            url = '/?c=WhsDocumentUcInvent&m=getWhsDocumentUcInventDrugInventoryLastNum',

            ajax_params = {
                WhsDocumentUcInvent_id: wnd.WhsDocumentUcInvent_id,
            };

        this.sendAjaxRequestPromise(ajax_params, url).then(function(ret){
            wnd.setFormFieldValue('WhsDocumentUcInventDrugInventory_InvNum',ret);
        })
    },

    initComponent: function() {

        var wnd = this,
            formName = wnd.formName,
            formPrefix = wnd.formPrefix;

        wnd[formName] = new Ext.form.FormPanel(
            {
                bodyStyle: '{padding-top: 0.5em;}',
                border: false,
                frame: true,
                labelAlign: 'right',
                layout: 'form',
                labelWidth: 1,
                id: formName,
                url: '/?c=WhsDocumentUcInvent&m=saveWhsDocumentUcInventDrugInventoryNum',
                autoLoad: false,
                items: [{
                    xtype: 'hidden',
                    name: 'WhsDocumentUcInventDrugInventory_id'
                },{
                    xtype: 'hidden',
                    name: 'WhsDocumentUcInvent_id'
                },
                    {
                        layout: 'column',
                        labelWidth: 1,
                        items: [
                            {
                                layout: 'form',
                                labelWidth: 100,
                                items: [{
                                    name: 'WhsDocumentUcInventDrugInventory_InvNum',
                                    allowBlank: false,
                                    width: 145,
                                    xtype: 'textfield',
                                    fieldLabel:'№ описи'
                                }]
                            },
                            {
                                layout: 'form',
                                bodyStyle: '{padding-left: 10px;}',
                                items: [{
                                    xtype: 'button',
                                    text: '+',
                                    width: 40,
                                    handler: function() {
                                        wnd.generateWhsDocumentUcInventNum();
                                    }
                                }]
                            }
                        ]
                    }
                ]
            });

        Ext.apply(this, {
            items: [
                this[this.formName]
            ],
            buttons: [{
                handler: function() {
                    this.doSave();
                }.createDelegate(this),
                iconCls: 'save16',
                id: formPrefix + '_SaveButton',
                text: BTN_FRMSAVE
            },
                '-',
                HelpButton(this, -1),
                {
                    handler: function() {
                        this.hide();
                    }.createDelegate(this),
                    iconCls: 'cancel16',
                    id: formPrefix + '_CancelButton',
                    text: BTN_FRMCANCEL
                }]
        });

        sw.Promed.swWhsDocumentUcInventDrugInventoryEditWindow.superclass.initComponent.apply(this, arguments);
    }
});
