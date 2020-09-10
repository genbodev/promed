/**
 * swDispNotificationsDiagEditWindow - окно редактирования групп диагнозов для рассылки напоминаний диспансеризцации
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Admin
 * @access			public
 * @copyright		Copyright (c) 2019 Swan Ltd.
 * @version			10.07.2019
 */

/*NO PARSE JSON*/

sw.Promed.swNoticeDiagGroupEditWindow = Ext.extend(sw.Promed.BaseForm, {
    id: 'swNoticeDiagGroupEditWindow',
    width: 640,
    autoHeight: true,
    modal: true,

    formStatus: 'edit',
    action: 'view',
    callback: Ext.emptyFn,

    listeners: {
        'hide': function() {
            var base_form = this.FormPanel.getForm();
            var DiagPanel = this.findById('NDGEW_DiagPanel');
            DiagPanel.items.each(function(fieldSet){
                fieldSet.items.each(function(item) {
                    base_form.items.removeKey(item.id);
                });
            });
            DiagPanel.removeAll();
            this.FormPanel.initFields();
            this.syncShadow();
        }
    },

    doSave: function(options) {
        options = options || {};
        var base_form = this.FormPanel.getForm();
        var DiagPanel = this.findById('NDGEW_DiagPanel');

        if ( !base_form.isValid() ) {
            sw.swMsg.show(
                {
                    buttons: Ext.Msg.OK,
                    fn: function()
                    {
                        this.FormPanel.getFirstInvalidEl().focus(true);
                    }.createDelegate(this),
                    icon: Ext.Msg.WARNING,
                    msg: ERR_INVFIELDS_MSG,
                    title: ERR_INVFIELDS_TIT
                });
            return false;
        }
        if ( DiagPanel.items.getCount() == 0 ) {
            sw.swMsg.show(
                {
                    buttons: Ext.Msg.OK,
                    icon: Ext.Msg.WARNING,
                    msg: langs('Должен быть указан хотя бы один диагноз.'),
                    title: ERR_INVFIELDS_TIT
                });
            return false;
        }

        var loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT_SAVE });

        var values = base_form.getValues();
        var NoticeDiagGroupData = [];

        for (var num in this.DiagState) {
            var diag_state = this.DiagState[num];

            if (diag_state.status == 1) {
                for (var field in diag_state.origValues) {
                    if (values[field+'_'+num] && values[field+'_'+num] != diag_state.origValues[field]) {
                        diag_state.status = 2;
                        break;
                    }
                }
            }

            switch (diag_state.status) {
                case 0:
                case 2:
                    NoticeDiagGroupData.push({
                        NoticeDiagGroupLink_id: values['NoticeDiagGroupLink_id_'+num],
                        NoticeDiagGroupLink_FromDiag_id: values['NoticeDiagGroupLink_FromDiag_id_'+num],
                        NoticeDiagGroupLink_ToDiag_id: values['NoticeDiagGroupLink_ToDiag_id_'+num]==undefined ? null : values['NoticeDiagGroupLink_ToDiag_id_'+num],
                        RecordStatus_Code: diag_state.status
                    });
                    break;
                case 3:
                    NoticeDiagGroupData.push({
                        NoticeDiagGroupLink_id: diag_state.NoticeDiagGroupLink_id,
                        NoticeDiagGroupLink_FromDiag_id: null,
                        NoticeDiagGroupLink_ToDiag_id: null,
                        RecordStatus_Code: diag_state.status
                    });
                    break;
            }

        }

        var data = {
            NoticeDiagGroup_id: values.NoticeDiagGroup_id,
            NoticeDiagGroup_Name: values.NoticeDiagGroup_Name,
            NoticeDiagGroupData: Ext.util.JSON.encode(NoticeDiagGroupData)
        };

        if (options.allowIntersection) {
            data.allowIntersection = options.allowIntersection;
        }

        Ext.Ajax.request({
            params: data,
            url: this.FormPanel.url,
            failure: function() {
                loadMask.hide();
            },
            success: function(response) {
                loadMask.hide();
                var responseObj = Ext.util.JSON.decode(response.responseText);
                if (responseObj.success) {
                    this.callback();
                    this.hide();
                } else if (responseObj.Alert_Msg) {
                    sw.swMsg.show({
                        buttons: Ext.Msg.OKCANCEL,
                        fn: function ( buttonId ) {
                            if ( buttonId == 'ok' ) {
                                switch ( responseObj.Alert_Code ) {
                                    case 1:
                                        options.allowIntersection = 1;
                                        break;
                                }
                                this.doSave(options);
                            }
                        }.createDelegate(this),
                        msg: responseObj.Alert_Msg,
                        title: langs('Вопрос')
                    });
                } else if (responseObj.Error_Msg) {
                    sw.swMsg.alert(langs('Ошибка'), responseObj.Error_Msg);
                }
            }.createDelegate(this)
        });
    },

    addDiagFieldSet: function(options) {
        var wnd = this;
        var base_form = this.FormPanel.getForm();
        var DiagPanel = this.findById('NDGEW_DiagPanel');

        this.DiagLastNum++;
        var num = this.DiagLastNum;

        this.DiagState[num] = {
            status: 0,
            NoticeDiagGroupLink_id: null,
            origValues: {NoticeDiagGroupLink_FromDiag_id: null, NoticeDiagGroupLink_ToDiag_id: null}
        };

        var config = {
            layout: 'form',
            id: 'DiagFieldSet_'+num,
            autoHeight: true,
            cls: 'NoticeDiagGroupFieldSet',
            width: 570,
            items: []
        };

        if (options && options.isRange) {
            config.items = [{
                html: '<div id="DiagHeader_'+num+'" class="NoticeDiagGroupFieldSetHeader">' +
                    '<div class="NoticeDiagGroupFieldSetBlock"></div>' +
                    '<div class="NoticeDiagGroupsFieldSetLabel">Диапазон диагнозов</div>' +
                    '<div class="NoticeDiagGroupFieldSetLine" style="width: 361px;"></div>' +
                    '</div>',
                style: 'margin-bottom: 5px;'
            }, {
                xtype: 'hidden',
                name: 'NoticeDiagGroupLink_id_'+num
            }, {
                allowBlank: false,
                xtype: 'swdiagcombo',
                fieldLabel: langs('От'),
                hiddenName: 'NoticeDiagGroupLink_FromDiag_id_'+num,
                anchor: '98%'
            }, {
                allowBlank: false,
                xtype: 'swdiagcombo',
                fieldLabel: langs('До'),
                hiddenName: 'NoticeDiagGroupLink_ToDiag_id_'+num,
                anchor: '98%'
            }];
        } else {
            config.items = [{
                html: '<div id="DiagHeader_'+num+'" class="NoticeDiagGroupFieldSetHeader">' +
                    '<div class="NoticeDiagGroupFieldSetBlock"></div>' +
                    '<div class="NoticeDiagGroupFieldSetLabel">Диагноз</div>' +
                    '<div class="NoticeDiagGroupFieldSetLine" style="width: 430px;"></div>' +
                    '</div>',
                style: 'margin-bottom: 5px;'
            }, {
                xtype: 'hidden',
                name: 'NoticeDiagGroupLink_id_'+num
            }, {
                allowBlank: false,
                xtype: 'swdiagcombo',
                fieldLabel: '',
                labelSeparator: '',
                hiddenName: 'NoticeDiagGroupLink_FromDiag_id_'+num,
                anchor: '98%'
            }];
        }

        var DiagFieldSet = DiagPanel.add(config);
        this.doLayout();
        this.syncSize();
        this.FormPanel.initFields();

        if (options && options.data) {
            this.DiagState[num].status = options.data.RecordStatus_Code;
            this.DiagState[num].NoticeDiagGroupLink_id = options.data.NoticeDiagGroupLink_id;
            this.DiagState[num].origValues.NoticeDiagGroupLink_FromDiag_id = options.data.NoticeDiagGroupLink_FromDiag_id;
            this.DiagState[num].origValues.NoticeDiagGroupLink_ToDiag_id = options.data.NoticeDiagGroupLink_ToDiag_id;

            base_form.findField('NoticeDiagGroupLink_id_'+num).setValue(options.data.NoticeDiagGroupLink_id);
            this.setDiagByName('NoticeDiagGroupLink_FromDiag_id_'+num, options.data.NoticeDiagGroupLink_FromDiag_id);
            if (options.isRange) {
                this.setDiagByName('NoticeDiagGroupLink_ToDiag_id_'+num, options.data.NoticeDiagGroupLink_ToDiag_id);
            }
        }

        var delButton = new Ext.Button({
            iconCls:'delete16',
            text: langs('Удалить'),
            style: 'display: inline-block; vertical-align: middle;',
            handler: function()
            {
                if (wnd.DiagState[num].status != 0) {
                    wnd.DiagState[num].status = 3;
                } else {
                    delete wnd.DiagState[num];
                }

                DiagFieldSet.items.each(function(item) {
                    wnd.FormPanel.getForm().items.removeKey(item.id);
                });
                DiagPanel.remove(DiagFieldSet.id);
                wnd.doLayout();
                wnd.syncShadow();
                wnd.FormPanel.initFields();
            }
        });
        delButton.render('DiagHeader_'+num);
    },

    setDiagByName: function(name, value) {
        var field = this.FormPanel.getForm().findField(name);

        field.getStore().load({
            params: { where: "where DiagLevel_id = 4 and Diag_id = " + value },
            callback: function() {
                field.getStore().each(function(record) {
                    if ( record.get('Diag_id') == value ) {
                        field.setValue(value);
                        field.fireEvent('select', field, record, 0);
                        field.fireEvent('change', field, value);
                    }
                });
            }
        });
    },

    show: function() {
        sw.Promed.swNoticeDiagGroupEditWindow.superclass.show.apply(this, arguments);

        var base_form = this.FormPanel.getForm();

        base_form.reset();

        this.DiagLastNum = 0;
        this.DiagState = {};
        this.callback = Ext.emptyFn;
        this.action = 'view';

        if (arguments[0].action) {
            this.action = arguments[0].action;
        }
        if (arguments[0].callback) {
            this.callback = arguments[0].callback;
        }
        if (arguments[0].formParams) {
            base_form.setValues(arguments[0].formParams);
        }

        var loadMask = new Ext.LoadMask(this.getEl(),{msg: LOAD_WAIT});
        loadMask.show();
        switch (this.action)
        {
            case 'add':
                this.setTitle(langs('Группа диагнозов: Добавление'));
                this.enableEdit(true);

                loadMask.hide();
                break;

            case 'edit':
            case 'view':
                if (this.action == 'edit') {
                    this.setTitle(langs('Группа диагнозов: Редактирование'));
                    this.enableEdit(true);
                } else {
                    this.setTitle(langs('Группа диагнозов: Просмотр'));
                    this.enableEdit(false);
                }

                base_form.load({
                    url: '/?c=NoticeDiagGroup&m=loadNoticeDiagGroupForm',
                    params: {NoticeDiagGroup_id: base_form.findField('NoticeDiagGroup_id').getValue()},
                    failure: function() {
                        loadMask.hide();
                        //
                    },
                    success: function(form, response) {
                        if (response.result.data.NoticeDiagGroupData) {
                            var DiagData = response.result.data.NoticeDiagGroupData;

                            for (var i = 0; i < DiagData.length; i++) {
                                this.addDiagFieldSet({
                                    data: DiagData[i],
                                    isRange: Ext.isEmpty(DiagData[i].NoticeDiagGroupLink_ToDiag_id) ? false : true
                                });
                            }
                        }
                        loadMask.hide();
                    }.createDelegate(this)
                });
                break;
        }
    },

    initComponent: function() {
        this.FormPanel = new Ext.form.FormPanel({
            bodyBorder: false,
            border: false,
            buttonAlign: 'left',
            frame: true,
            id: 'NDGEW_NoticeDiagGroupEditForm',
            url: '/?c=NoticeDiagGroup&m=saveNoticeDiagGroup',
            bodyStyle: 'padding: 10px 5px 10px 20px;',
            labelAlign: 'right',

            items: [{
                xtype: 'hidden',
                name: 'NoticeDiagGroup_id'
            }, {
                allowBlank: false,
                xtype: 'textfield',
                fieldLabel: langs('Название группы'),
                name: 'NoticeDiagGroup_Name',
                width: 320
            }, {
                layout: 'form',
                id: 'NDGEW_DiagPanel',
                cls: 'NoticeDiagGroupPanel',
                autoHeight: true,
                items: []
            }, {
                layout: 'column',
                id: 'NDGEW_ButtonDiagPanel',
                cls: 'NoticeDiagGroupFieldSet',
                height: 25,
                style: 'margin-left: 100px; margin-top: 10px;',
                items: [{
                    layout: 'form',
                    items: [{
                        xtype: 'button',
                        iconCls:'add16',
                        text: langs('Добавить диапазон диагнозов'),
                        handler: function() {
                            this.addDiagFieldSet({isRange: true});
                        }.createDelegate(this)
                    }]
                }, {
                    layout: 'form',
                    style: 'margin-left: 10px',
                    items: [{
                        xtype: 'button',
                        iconCls:'add16',
                        text: langs('Добавить диагноз'),
                        handler: function() {
                            this.addDiagFieldSet({isRange: false});
                        }.createDelegate(this)
                    }]
                }]
            }],
            reader: new Ext.data.JsonReader({
                success: function() {}
            }, [
                {name: 'NoticeDiagGroup_id'},
                {name: 'NoticeDiagGroup_Name'},
                {name: 'NoticeDiagGroupData'}
            ]),
            keys: [{
                fn: function(e) { this.doSave(); }.createDelegate(this),
                key: Ext.EventObject.ENTER,
                stopEvent: true
            }]
        });

        Ext.apply(this, {
            items: [ this.FormPanel ],
            buttons: [
                {
                    text: BTN_FRMSAVE,
                    id: 'NDGEW_ButtonSave',
                    tooltip: langs('Сохранить'),
                    iconCls: 'save16',
                    handler: function()
                    {
                        this.doSave();
                    }.createDelegate(this)
                }, { text: '-' },
                HelpButton(this, 1),
                {
                    handler: function () {
                        this.hide();
                    }.createDelegate(this),
                    iconCls: 'cancel16',
                    id: 'NDGEW_CancelButton',
                    text: langs('Отменить')
                }]
        });

        sw.Promed.swNoticeDiagGroupEditWindow.superclass.initComponent.apply(this, arguments);
    }
});