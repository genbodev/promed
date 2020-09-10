/**
 * swGoodsStorageEditWindow - окно редактирования регионального кода МНН
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2014 Swan Ltd.
 * @author       Salakhov R.
 * @version      09.2014
 * @comment
 */
sw.Promed.swGoodsStorageEditWindow = Ext.extend(sw.Promed.BaseForm, {
    autoHeight: false,
    title: lang['naimenovaniya_mest_hraneniya_redaktirovanie'],
    layout: 'border',
    id: 'GoodsStorageEditWindow',
    modal: true,
    shim: false,
    width: 500,
    height: 210,
    resizable: false,
    maximizable: false,
    maximized: false,
    listeners: {
        hide: function() {
            this.onHide();
        },
        show: function(wnd) {
            wnd.form.findField('StorageUnitType_Name').focus(true, 50);
        }
    },
    onHide: Ext.emptyFn,
    doSave:  function() {
        var wnd = this;
        if ( !this.form.isValid() ) {
            sw.swMsg.show({
                buttons: Ext.Msg.OK,
                fn: function() {
                    wnd.findById('GoodsStorageEditForm').getFirstInvalidEl().focus(true);
                },
                icon: Ext.Msg.WARNING,
                msg: ERR_INVFIELDS_MSG,
                title: ERR_INVFIELDS_TIT
            });
            return false;
        }
        this.submit();
        return true;
    },
    submit: function() {
        var wnd = this;
        var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение..."});
        loadMask.show();
        var params = {};
        params.action = wnd.action;
        this.form.submit({
            params: params,
            failure: function(result_form, action) {
                loadMask.hide();
                if (action.result) {
                    if (action.result.Error_Code) {
                        Ext.Msg.alert(lang['oshibka_#']+action.result.Error_Code, action.result.Error_Message);
                    }
                }
            },
            success: function(result_form, action) {
                loadMask.hide();
                wnd.callback(wnd.owner, action.result.StorageUnitType_id);
                wnd.hide();
            }
        });
    },
    enableEdit: function(enable) {
        if (enable) {
            this.form.findField('StorageUnitType_Name').enable();
            this.form.findField('StorageUnitType_Nick').enable();
            this.form.findField('StorageUnitType_Code').enable();
            this.form.findField('dateRange').enable();
            this.buttons[0].enable();
        } else {
            this.form.findField('StorageUnitType_Name').disable();
            this.form.findField('StorageUnitType_Nick').disable();
            this.form.findField('StorageUnitType_Code').disable();
            this.form.findField('dateRange').disable();
            this.buttons[0].disable();
        }
    },
    show: function() {
        var wnd = this;
        sw.Promed.swGoodsStorageEditWindow.superclass.show.apply(this, arguments);
        this.action = '';
        this.callback = Ext.emptyFn;
        this.StorageUnitType_id = null;
        if ( !arguments[0] ) {
            sw.swMsg.alert(lang['oshibka'], lang['ne_ukazanyi_vhodnyie_dannyie'], function() { wnd.hide(); });
            return false;
        }
        if ( arguments[0].action ) {
            this.action = arguments[0].action;
        }
        if ( arguments[0].callback && typeof arguments[0].callback == 'function' ) {
            this.callback = arguments[0].callback;
        }
        if ( arguments[0].owner ) {
            this.owner = arguments[0].owner;
        }
        if ( arguments[0].StorageUnitType_id ) {
            this.StorageUnitType_id = arguments[0].StorageUnitType_id;
        }
        this.form.reset();
        this.title = lang['naimenovaniya_mest_hraneniya'];
        var loadMask = new Ext.LoadMask(this.form.getEl(), {msg:lang['zagruzka']});
        loadMask.show();
        switch (this.action) {
            case 'add':
                this.setTitle(this.title+lang['_dobavlenie']);
                this.enableEdit(true);
                wnd.form.findField('dateRange').setValue(getGlobalOptions().date) ;
                loadMask.hide();
                break;
            case 'edit':
            case 'view':
                this.setTitle(this.title+(this.action == 'edit' ? lang['_redaktirovanie'] : lang['_prosmotr']));
                this.enableEdit(this.action == 'edit');
                Ext.Ajax.request({
                    failure:function () {
                        sw.swMsg.alert(lang['oshibka'], lang['ne_udalos_poluchit_dannyie_s_servera']);
                        loadMask.hide();
                        wnd.hide();
                    },
                    params:{
                        StorageUnitType_id: wnd.StorageUnitType_id
                    },
                    success: function (response) {
                        var result = Ext.util.JSON.decode(response.responseText);
                        if (!result[0]) {
                            return false;
                        }
                        wnd.form.setValues(result[0]);
                        loadMask.hide();
                        return true;
                    },
                    url:'/?c=Storage&m=loadGoodsStorage'
                });
                break;
        }
        return true;
    },
    initComponent: function() {
        var wnd = this;

        var form = new Ext.Panel({
            autoScroll: true,
            bodyBorder: false,
            bodyStyle: 'padding: 5px 5px 0',
            border: false,
            frame: true,
            region: 'center',
            items: [{
                xtype: 'form',
                autoHeight: true,
                id: 'GoodsStorageEditForm',
                style: 'margin-bottom: 0.5em;',
                bodyStyle:'background:#DFE8F6;padding:5px;',
                border: true,
                labelWidth: 150,
                labelAlign: 'right',
                collapsible: true,
                region: 'north',
                url:'/?c=Storage&m=saveGoodsStorage',
                items: [{
                    name: 'StorageUnitType_id',
                    xtype: 'hidden',
                    value: 0
                }, {
                    fieldLabel: lang['naimenovanie'],
                    hiddenName: 'StorageUnitType_Name',
                    name: 'StorageUnitType_Name',
                    allowBlank: false,
                    xtype: 'textfield',
                    width: 180
                }, {
                    fieldLabel: lang['kratkoe_naimnovanie'],
                    hiddenName: 'StorageUnitType_Nick',
                    name: 'StorageUnitType_Nick',
                    allowBlank: false,
                    xtype: 'textfield',
                    width: 180
                }, {
                    fieldLabel: lang['kod'],
                    hiddenName: 'StorageUnitType_Code',
                    name: 'StorageUnitType_Code',
                    allowBlank: false,
                    xtype: 'textfield',
                    width: 180
                }, {
                    fieldLabel: lang['period'],
                    hiddenName: 'dateRange',
                    name: 'dateRange',
                    plugins: [new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
                    xtype: 'daterangefield',
                    width: 180
                }]
            }],
            reader: new Ext.data.JsonReader({
                success: Ext.emptyFn
            }, [
                {name: 'StorageUnitType_id'},
                {name: 'StorageUnitType_Name'},
                {name: 'StorageUnitType_Nick'},
                {name: 'StorageUnitType_Code'},
                {name: 'dateRange'}
            ])
        });
        Ext.apply(this, {
            layout: 'border',
            buttons: [
                {
                    handler: function() {
                        this.ownerCt.doSave();
                    },
                    iconCls: 'save16',
                    text: BTN_FRMSAVE
                },
                {
                    text: '-'
                },
                HelpButton(this, 0),
                {
                    handler: function() {
                        this.ownerCt.hide();
                    },
                    iconCls: 'cancel16',
                    text: BTN_FRMCANCEL
                }
            ],
            items:[form]
        });
        sw.Promed.swGoodsStorageEditWindow.superclass.initComponent.apply(this, arguments);
        this.form = this.findById('GoodsStorageEditForm').getForm();
    }
});