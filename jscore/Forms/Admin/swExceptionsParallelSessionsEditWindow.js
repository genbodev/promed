/**
 * swExceptionsParallelSessionsEditWindow - окно редактирования исключений для параллельных сессий
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Admin
 * @access			public
 * @copyright		Copyright (c) 2013 Swan Ltd.
 * @author			Yavorskiy Maksim (m.yavorskiy@swan.perm.ru)
 * @version			16.10.2019
 */

sw.Promed.swExceptionsParallelSessionsEditWindow = Ext.extend(sw.Promed.BaseForm, {
    autoHeight: true,
    autoScroll: true,
    callback: Ext.emptyFn,
    closable: true,
    closeAction: 'hide',
    draggable: true,
    maximizable: false,
    modal: true,
    resizable: false,
    width: 600,

    doSave: function() {
        var base_form = this.FormPanel.getForm();
        var wnd = this;

        if ( !base_form.isValid() ) {
            sw.swMsg.show({
                buttons: Ext.Msg.OK,
                fn: function() {
                    wnd.FormPanel.getFirstInvalidEl().focus(false);
                },
                icon: Ext.Msg.WARNING,
                msg: ERR_INVFIELDS_MSG,
                title: ERR_INVFIELDS_TIT
            });
            return false;
        }

        wnd.getLoadMask().show();

        var params = new Object();

        params = base_form.getValues();

        base_form.submit({
            failure: function(result_form, action) {
                wnd.getLoadMask().hide();
            },
            params: params,
            success: function(result_form, action) {
                wnd.getLoadMask().hide();

                if ( action.result ) {
                    wnd.callback();
                    wnd.hide();
                }
                else {
                    sw.swMsg.alert(langs('Ошибка'), langs('При сохранении произошли ошибки'));
                }
            }
        });
    },

    show: function() {
        sw.Promed.swExceptionsParallelSessionsEditWindow.superclass.show.apply(this, arguments);

        this.action = null;
        var form = this;
        var base_form = form.FormPanel.getForm();

        if ( arguments && arguments[0].action ) {
            this.action = arguments[0].action;
        }

        if ( arguments && arguments[0].callback ) {
            this.callback = arguments[0].callback;
        }

        base_form.reset();

        if ( this.action != 'add' && arguments[0].formParams ) {
            base_form.setValues(arguments[0].formParams);
        }

        var loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT });
        loadMask.show();

        switch ( this.action ) {
            case 'add':
                this.enableEdit(true);
                this.setTitle(langs('Исключение: Добавление'));

                base_form.clearInvalid();
                base_form.findField('IPSessionCount_IP').focus(true, 250);
                base_form.findField('IPSessionCount_Max').setValue(1);

                loadMask.hide();
                break;

            case 'edit':
            case 'view':
                var ParallSessions_id = base_form.findField('IPSessionCount_id').getValue();

                if ( !ParallSessions_id ) {
                    loadMask.hide();
                    this.hide();
                    return false;
                }

                var afterFormLoad = function() {
                    loadMask.hide();
                    if ( form.action == 'edit' ) {
                        form.setTitle(langs('Исключение: Редактирование'));
                        form.enableEdit(true);
                    }
                    else {
                        form.setTitle(langs('Исключение: Просмотр'));
                        form.enableEdit(false);
                    }
                    base_form.clearInvalid();
                    if ( form.action == 'edit' ) {
                        base_form.findField('IPSessionCount_IP').focus(true, 250);
                    }
                    else {
                        form.buttons[form.buttons.length - 1].focus();
                    }
                };

                base_form.load({
                    params: {IPSessionCount_id: ParallSessions_id},
                    failure: function() {
                        afterFormLoad();
                    },
                    success: function() {
                        afterFormLoad();
                    },
                    url: '/?c=IPSessionCount&m=loadIPSessionCountEditForm'
                });

                break;

            default:
                this.hide();
                break;
        }
    },

    initComponent: function() {
        var form = this;

        this.FormPanel = new Ext.form.FormPanel({
            bodyStyle: '{padding-top: 0.5em;}',
            border: false,
            frame: true,
            labelAlign: 'right',
            labelWidth: 200,
            layout: 'form',
            url: '/?c=IPSessionCount&m=saveIPSessionCount',
            autoLoad: false,
            reader: new Ext.data.JsonReader({
                success: Ext.emptyFn
            }, [
                { name: 'IPSessionCount_id' },
                { name: 'IPSessionCount_IP' },
                { name: 'IPSessionCount_Max' },
            ]),
            items: [
                {
                    name: 'IPSessionCount_id',
                    xtype: 'hidden'
                }, {
                    allowBlank: false,
                    fieldLabel: langs('IP-адрес'),
                    name: 'IPSessionCount_IP',
                    xtype: 'textfield',
                    width: 100
                }, {
                    allowBlank: false,
                    fieldLabel: langs('Количество параллельных сеансов доступа'),
                    allowNegative: false,
                    name: 'IPSessionCount_Max',
                    xtype: 'textfield',
                    width: 250
                }
            ]
        });

        Ext.apply(this, {
            items: [
                this.FormPanel
            ],
            buttons: [{
                handler: function() {
                    this.doSave();
                }.createDelegate(this),
                iconCls: 'save16',
                id: 'CIEW_SaveButton',
                text: BTN_FRMSAVE
            },
                '-',
                HelpButton(this, -1),
                {
                    handler: function() {
                        this.hide();
                    }.createDelegate(this),
                    iconCls: 'cancel16',
                    id: 'CIEW_CancelButton',
                    text: BTN_FRMCANCEL
                }]
        });

        sw.Promed.swExceptionsParallelSessionsEditWindow.superclass.initComponent.apply(this, arguments);
    }
});
