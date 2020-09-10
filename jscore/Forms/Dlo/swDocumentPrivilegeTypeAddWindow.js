/**
 * swDocumentPrivilegeTypeAddWindow - окно добавления нового типа документа о праве на льготу
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Dlo
 * @access       public
 * @copyright    Copyright (c) 2019 Swan Ltd.
 * @author       Salakhov R.
 * @version      09.2019
 * @comment
 */
sw.Promed.swDocumentPrivilegeTypeAddWindow = Ext.extend(sw.Promed.BaseForm, {
    autoHeight: false,
    title: 'Тип документа о праве на льготу: Добавление',
    layout: 'border',
    id: 'DocumentPrivilegeTypeAddWindow',
    modal: true,
    shim: false,
    width: 450,
    height: 109,
    resizable: false,
    maximizable: false,
    maximized: false,
    doSave:  function() {
        var wnd = this;
        if ( !this.form.isValid() ) {
            sw.swMsg.show({
                buttons: Ext.Msg.OK,
                fn: function() {
                    wnd.findById('DocumentPrivilegeTypeAddForm').getFirstInvalidEl().focus(true);
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
        var params = new Object();

        wnd.getLoadMask('Подождите, идет сохранение...').show();
        this.form.submit({
            params: params,
            failure: function(result_form, action) {
                wnd.getLoadMask().hide();
                if (action.result) {
                    if (action.result.Error_Code) {
                        Ext.Msg.alert('Ошибка #'+action.result.Error_Code, action.result.Error_Message);
                    }
                }
            },
            success: function(result_form, action) {
                wnd.getLoadMask().hide();
                if (action.result && action.result.DocumentPrivilegeType_id > 0) {
                    var id = action.result.DocumentPrivilegeType_id;
                    wnd.form.findField('DocumentPrivilegeType_id').setValue(id);
                    wnd.callback({
                        DocumentPrivilegeType_id: id
                    });
                    wnd.hide();
                }
            }
        });
    },
    show: function() {
        var wnd = this;
        sw.Promed.swDocumentPrivilegeTypeAddWindow.superclass.show.apply(this, arguments);
        this.callback = Ext.emptyFn;
        this.DocumentPrivilegeType_id = null;
        if ( !arguments[0] ) {
            sw.swMsg.alert('Ошибка', 'Не указаны входные данные', function() { wnd.hide(); });
            return false;
        }
        if ( arguments[0].callback && typeof arguments[0].callback == 'function' ) {
            this.callback = arguments[0].callback;
        }
        if ( arguments[0].DocumentPrivilegeType_id ) {
            this.DocumentPrivilegeType_id = arguments[0].DocumentPrivilegeType_id;
        }
        this.form.reset();
    },
    initComponent: function() {
        var form = new Ext.form.FormPanel({
            url: '/?c=Privilege&m=saveDocumentPrivilegeType',
            region: 'center',
            autoHeight: true,
            frame: true,
            labelAlign: 'right',
            labelWidth: 100,
            bodyStyle: 'padding: 5px 5px 0',
            items: [{
                xtype: 'hidden',
                name: 'DocumentPrivilegeType_id'
            }, {
                xtype: 'textfield',
                fieldLabel: langs('Наименование'),
                name: 'DocumentPrivilegeType_Name',
                allowBlank: false,
                maxLength: 100,
                width: 300
            }]
        });

        Ext.apply(this, {
            layout: 'border',
            buttons:
                [{
                    handler: function()
                    {
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
                        handler: function()
                        {
                            this.ownerCt.hide();
                        },
                        iconCls: 'cancel16',
                        text: BTN_FRMCANCEL
                    }],
            items:[form]
        });
        sw.Promed.swDocumentPrivilegeTypeAddWindow.superclass.initComponent.apply(this, arguments);
        this.form = form.getForm();
    }
});