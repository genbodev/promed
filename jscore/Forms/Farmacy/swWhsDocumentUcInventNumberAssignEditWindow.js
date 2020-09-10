/**
 * swWhsDocumentUcInventNumberAssignEditWindow - окно формы назначения номера описи для позиций инв. ведомости
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Admin
 * @access			public
 * @copyright		Copyright (c) 2017 Swan Ltd.
 * @author			Max Sysolin (max.sysolin@gmail.com)
 * @version			25.05.2017
 */

sw.Promed.swWhsDocumentUcInventNumberAssignEditWindow = Ext.extend(sw.Promed.BaseForm, {

    id: 'WhsDocumentUcInventNumberAssignEditWindow',
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
    formName: 'WhsDocumentUcInventNumberAssignEditForm',
    // краткое имя формы (для айдишников)
    formPrefix: 'WDUINAEW_',

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

        var combo = wnd.getFormField('WhsDocumentUcInventDrug_InvNum');
        var comboId = combo.findRecord('WhsDocumentUcInventDrug_InvNum', combo.getValue());
        if (!form.isValid() || !comboId) {

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
                WhsDocumentUcInventDrug_List : wnd.WhsDocumentUcInventDrug_List.length > 0 ? wnd.WhsDocumentUcInventDrug_List.join(',') : null,
                WhsDocumentUcInventDrugInventory_id: comboId.data.WhsDocumentUcInventDrugInventory_id
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

        sw.Promed.swWhsDocumentUcInventNumberAssignEditWindow.superclass.show.apply(this, arguments);

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

        // оптимизировал присвоение параметров arguments[0]
        for (var field_name in args) {
            //log(field_name +':'+ args[field_name]);
            wnd[field_name] = args[field_name];
        }

        loadMask.show();
        wnd.getFormField('WhsDocumentUcInventDrug_InvNum').getStore().load({

            params: {
                WhsDocumentUcInvent_id: wnd.WhsDocumentUcInvent_id
            },
            callback: function(){}
        });

        wnd.setTitle('Включение медикаментов в опись');

        form.clearInvalid();
        loadMask.hide();
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
            url = '/?c=WhsDocumentUcInvent&m=getWhsDocumentUcInventLastNum',

            ajax_params = {
                WhsDocumentUcInvent_id: wnd.WhsDocumentUcInvent_id,
            };

        this.sendAjaxRequestPromise(ajax_params, url).then(function(ret){
            wnd.setFormFieldValue('WhsDocumentUcInventDrug_InvNum',ret);
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
                url: '/?c=WhsDocumentUcInvent&m=assignWhsDocumentUcInventNumber',
                autoLoad: false,
                items: [
                    {
                        layout: 'column',
                        labelWidth: 1,
                        items: [
                            {
                                layout: 'form',
                                labelWidth: 60,
                                items: [{
                                    hiddenName: 'WhsDocumentUcInventDrug_InvNum',
                                    allowBlank: false,
                                    width: 220,
                                    xtype: 'combo',
                                    store:
                                        new sw.Promed.Store({
                                            autoLoad: false,
                                            url: '/?c=WhsDocumentUcInvent&m=getWhsDocumentUcInventNumbers',
                                            fields: [
                                                {name: 'WhsDocumentUcInventDrug_InvNum', type: 'int'},
                                                {name: 'WhsDocumentUcInventDrugInventory_id', hidden:true, type: 'int'},
                                                {name: 'StorageWork_Person', type: 'string'},
                                            ],
                                            key: 'Device_id',
                                        }),
                                    valueField: 'WhsDocumentUcInventDrug_InvNum',
                                    displayField: 'WhsDocumentUcInventDrug_InvNum',
                                    triggerAction: 'all',
                                    editable: false,
                                    tpl:
                                    '<tpl for="."><div class="x-combo-list-item">'+
                                    '{WhsDocumentUcInventDrug_InvNum}&nbsp;<font color="#00008b">{StorageWork_Person}</font>'+
                                    '</div></tpl>',
                                    labelSeparator: '',
                                    fieldLabel: '№ описи ',
                                    listeners: {
                                    },
                                    /*
                                    setValue: function(v){
                                        var text = v;
                                        if(this.valueField){
                                            var r = this.findRecord(this.valueField, text);
                                            if(r){
                                                text = r.data[this.displayField];
                                                text = text + '. ' + r.data['StorageWork_Person'];
                                            }
                                            debugger;
                                        }
                                        this.lastSelectionText = text;
                                        Ext.form.ComboBox.superclass.setValue.call(this, text);
                                    }
                                    */
                                }]
                            },
                            /*
                            {
                                layout: 'form',
                                bodyStyle: '{padding-left: 10px;}',
                                hidden: true,
                                items: [{
                                    xtype: 'button',
                                    text: '+',
                                    width: 40,
                                    handler: function() {
                                        wnd.generateWhsDocumentUcInventNum();
                                    }
                                }]
                            }
                            */
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

        sw.Promed.swWhsDocumentUcInventNumberAssignEditWindow.superclass.initComponent.apply(this, arguments);
    }
});
