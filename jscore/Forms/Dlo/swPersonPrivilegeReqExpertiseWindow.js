/**
 * swPersonPrivilegeReqExpertiseWindow - окно редактирования запроса на включение в льготный регистр
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
sw.Promed.swPersonPrivilegeReqExpertiseWindow = Ext.extend(sw.Promed.BaseForm, {
    autoHeight: false,
    title: 'Экспертиза запроса на включение в льготный регистр',
    layout: 'border',
    id: 'PersonPrivilegeReqExpertiseWindow',
    modal: true,
    shim: false,
    width: 400,
    resizable: false,
    maximizable: true,
    maximized: true,
    doSave:  function(options) {
        var wnd = this;
        if (!this.res_form.isValid()) {
            sw.swMsg.show({
                buttons: Ext.Msg.OK,
                fn: function() {
                    wnd.findById('PersonPrivilegeReqExpertiseForm').getFirstInvalidEl().focus(true);
                },
                icon: Ext.Msg.WARNING,
                msg: ERR_INVFIELDS_MSG,
                title: ERR_INVFIELDS_TIT
            });
            return false;
        }
        this.submit(options);
        return true;
    },
    submit: function(options) {
        var wnd = this;
        var params = new Object();

        if (options && options.action) {
            params.action = options.action;
        }
        params.PersonPrivilegeReq_id = wnd.PersonPrivilegeReq_id;
        params.PersonPrivilegeReq_endDT = !Ext.isEmpty(wnd.form.findField('PersonPrivilegeReq_endDT').getValue()) ? wnd.form.findField('PersonPrivilegeReq_endDT').getValue().format('d.m.Y') : null;
        params.PersonPrivilegeReqAns_DeclCause = wnd.decl_cause_combo.getSelectedRecordField('DeclCause_Name');

        wnd.getLoadMask('Подождите, идет сохранение...').show();
        this.res_form.submit({
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
                if (action.result && action.result.PersonPrivilegeReq_id > 0) {
                    var id = action.result.PersonPrivilegeReq_id;
                    wnd.form.findField('PersonPrivilegeReq_id').setValue(id);
                    wnd.callback(wnd.owner, id);
                    wnd.hide();
                }
            }
        });
    },
    setButtonsDisabled: function() {
        var rej_field = this.decl_cause_combo;
        if (Ext.isEmpty(rej_field.getValue())) {
            this.buttons[0].enable();
            this.buttons[1].disable();
        } else {
            this.buttons[0].disable();
            this.buttons[1].enable();
        }
    },
    setCommonInformationData: function(data) { //функция установки данных в блоке "Запрос подан"
        this.CommonInformationPanel.clearData();
        if (!Ext.isEmpty(data)) { //если пришли данные, то просто оборажаем их
            this.CommonInformationPanel.setData('request_dt', data.PersonPrivilegeReq_setDT);
            this.CommonInformationPanel.setData('lpu_name', !Ext.isEmpty(data.Msf_Lpu_Nick) ? data.Msf_Lpu_Nick+', ' : '');
            this.CommonInformationPanel.setData('post_name', !Ext.isEmpty(data.Msf_PostMed_Name) ? data.Msf_PostMed_Name+', ' : '');
            this.CommonInformationPanel.setData('med_personal_name', !Ext.isEmpty(data.Msf_Person_Fio) ? data.Msf_Person_Fio : '');
        }
        this.CommonInformationPanel.showData();
    },
    setModerationMode: function() { //настройка формы в зависимости от установленного в настройках типа модерации
        if (this.postmoderation_mode) {
            this.res_form.url = '/?c=Privilege&m=savePersonPrivilegeReqExpertisePM';
            this.form.findField('PersonPrivilegeReq_endDT').enable();
        } else {
            this.res_form.url = '/?c=Privilege&m=savePersonPrivilegeReqExpertise';
            this.form.findField('PersonPrivilegeReq_endDT').disable();
        }
    },
    show: function() {
        var wnd = this;
        sw.Promed.swPersonPrivilegeReqExpertiseWindow.superclass.show.apply(this, arguments);
        this.callback = Ext.emptyFn;
        this.PersonPrivilegeReq_id = null;
        this.Person_id = null;
        this.PersonPrivilegeReqStatus_id = null;
        this.PersonPrivilegeReqAns_IsInReg = null;
        this.postmoderation_mode = !Ext.isEmpty(getGlobalOptions().person_privilege_add_request_postmoderation);

        if ( !arguments[0] ) {
            sw.swMsg.alert('Ошибка', 'Не указаны входные данные', function() { wnd.hide(); });
            return false;
        }
        if ( arguments[0].callback && typeof arguments[0].callback == 'function' ) {
            this.callback = arguments[0].callback;
        }
        if ( arguments[0].owner ) {
            this.owner = arguments[0].owner;
        }
        if ( arguments[0].PersonPrivilegeReq_id ) {
            this.PersonPrivilegeReq_id = arguments[0].PersonPrivilegeReq_id;
        }
        if ( arguments[0].Person_id ) {
            this.Person_id = arguments[0].Person_id;
        }

        this.form.reset();
        this.res_form.reset();
        this.FileUploadPanel.reset();
        this.FileUploadPanel.disable();
        this.setModerationMode();
        this.setButtonsDisabled();

        var loadMask = new Ext.LoadMask(this.form.getEl(), {msg:'Загрузка...'});
        loadMask.show();

        this.FileUploadPanel.listParams = {
            ObjectName: 'PersonPrivilegeReq',
            ObjectID: wnd.PersonPrivilegeReq_id,
            add_empty_combo: false,
            callback: function() {
                wnd.FileUploadPanel.disable();
            }
        };
        this.FileUploadPanel.loadData();
        Ext.Ajax.request({
            params:{
                PersonPrivilegeReq_id: wnd.PersonPrivilegeReq_id
            },
            url:'/?c=Privilege&m=loadPersonPrivilegeReq',
            success: function (response) {
                var result = Ext.util.JSON.decode(response.responseText);
                if (!result[0]) {
                    return false
                }
                wnd.form.setValues(result[0]);
                wnd.res_form.setValues(result[0]);

                if (!Ext.isEmpty(result[0].Person_id)) {
                    wnd.Person_id = result[0].Person_id;
                    wnd.PersonPanel.load({
                        Person_id: result[0].Person_id
                    });
                }
                if (!Ext.isEmpty(result[0].Diag_id)) {
                    wnd.form.findField('Diag_id').getStore().load({
                        callback: function() {
                            var index = wnd.form.findField('Diag_id').getStore().findBy(function(rec) {
                                return (rec.get('Diag_id') == result[0].Diag_id);
                            });
                            if (index >= 0) {
                                wnd.form.findField('Diag_id').setValue(result[0].Diag_id);
                                wnd.form.findField('Diag_id').fireEvent('select', wnd.form.findField('Diag_id'), wnd.form.findField('Diag_id').getStore().getAt(index));
                            }
                        },
                        params: { where: "where Diag_id = " + result[0].Diag_id }
                    });
                }
                if (!Ext.isEmpty(result[0].PersonPrivilegeReq_begDT)) {
                    wnd.form.findField('PersonPrivilegeReq_begDT').setValue(result[0].PersonPrivilegeReq_begDT);
                }
                if (!Ext.isEmpty(result[0].PersonPrivilegeReq_endDT)) {
                    wnd.form.findField('PersonPrivilegeReq_endDT').setValue(result[0].PersonPrivilegeReq_endDT);
                }
                if (!Ext.isEmpty(result[0].PersonSurNameAtBirth_SurName)) {
                    wnd.form.findField('PersonSurNameAtBirth_SurName').showContainer();
                } else {
					wnd.form.findField('PersonSurNameAtBirth_SurName').hideContainer();
                }
                wnd.PersonPrivilegeReqAns_IsInReg = result[0].PersonPrivilegeReqAns_IsInReg;

                wnd.setButtonsDisabled();
                wnd.setCommonInformationData({
                    PersonPrivilegeReq_setDT: result[0].PersonPrivilegeReq_setDT,
                    Msf_Person_Fio: result[0].Msf_Person_Fio,
                    Msf_Lpu_Nick: result[0].Msf_Lpu_Nick,
                    Msf_PostMed_Name: result[0].Msf_PostMed_Name
                });

                wnd.privilege_type_combo.setLinkedFields();

                loadMask.hide();
            },
            failure:function () {
                sw.swMsg.alert(langs('Ошибка'), langs('Не удалось получить данные с сервера'));
                loadMask.hide();
                wnd.hide();
            }
        });
    },
    initComponent: function() {
        var wnd = this;

		wnd.PersonPanel = new sw.Promed.PersonInformationPanel({
			id: 'pprex_PersonPanel',
			region: 'north',
			style: 'padding-left: 10px; padding-top: 5px;'
		});
		wnd.PersonPanel.ButtonPanel.hide();

        wnd.FileUploadPanel = new sw.Promed.FileUploadPanel({
            win: this,
            width: 1000,
            buttonAlign: 'left',
            buttonLeftMargin: 100,
            labelWidth: 150,
            folder: 'pmmedia/',
            fieldsPrefix: 'pmMediaData',
            id: 'pprex_FileUploadPanel',
            style: 'background: transparent;',
            dataUrl: '/?c=PMMediaData&m=loadpmMediaDataListGrid',
            saveUrl: '/?c=PMMediaData&m=uploadFile',
            saveChangesUrl: '/?c=PMMediaData&m=saveChanges',
            deleteUrl: '/?c=PMMediaData&m=deleteFile',
            limitCountCombo: 30,
            listParams: {
                ObjectName: 'PersonPrivilegeReq',
                ObjectID: null
            }
        });

        this.CommonInformationPanel = new sw.Promed.HtmlTemplatePanel({
            win: this,
            frame: false
        });
        var tpl = "";
        tpl += "<table style='margin: 5px; float: left;'>";
        tpl += "<tr><td>{request_dt}</td></tr>";
        tpl += "<tr><td>{lpu_name} {post_name} {med_personal_name}</td></tr>";
        tpl += "</table>";
        this.CommonInformationPanel.setTemplate(tpl);

        this.privilege_type_combo = new sw.Promed.SwPrivilegeTypeCombo({
            xtype: 'swprivilegetypecombo',
            fieldLabel: 'Льготная категория',
            hiddenName: 'PrivilegeType_id',
            width: 300,
            disabled: true,
            listeners: {
                change: function() {
                    this.setLinkedFields();
                },
                select: function() {
                    this.setLinkedFields();
                }
            },
            getSelectedRecordData: function() {
                var combo = this;
                var value = combo.getValue();
                var data = new Object();
                if (value > 0) {
                    var idx = this.getStore().findBy(function(record) {
                        return (record.get(combo.valueField) == value);
                    })
                    if (idx > -1) {
                        Ext.apply(data, this.getStore().getAt(idx).data);
                    }
                }
                return data;
            },
            setLinkedFields: function() {
                var doc_visible = false;
                var diag_visible = false;

                if (this.getValue() > 0) {
                    var privilege_data = this.getSelectedRecordData();

                    //льгота явдялется федеральной или льгота является региональной и имеет признак «Документ на льготу»; настройка "Льготы социальные. Контроль на наличие данных документа, подтверждающего наличие льгот" включена
                    doc_visible = ((privilege_data && privilege_data.ReceptFinance_id == 1) || (privilege_data && privilege_data.ReceptFinance_id == 2 && privilege_data.PrivilegeType_IsDoc == 2 && getGlobalOptions().social_privilege_document_available_checking));

                    //льгота является региональной и имеет признак «Нозология»; настройка "Льготы по нозологиям. Контроль на наличие диагноза" включена
                    diag_visible = (privilege_data && privilege_data.ReceptFinance_id == 2 && privilege_data.PrivilegeType_IsNoz == 2 && getGlobalOptions().vzn_privilege_diag_available_checking);
                }

                if (doc_visible) {
                    wnd.form.findField('DocumentPrivilegeType_Name').ownerCt.show();
                } else {
                    wnd.form.findField('DocumentPrivilegeType_Name').ownerCt.hide();
                }
                if (diag_visible) {
                    wnd.form.findField('Diag_id').showContainer();
                } else {
                    wnd.form.findField('Diag_id').hideContainer();
                }
            }
        });

        this.decl_cause_combo = new sw.Promed.SwBaseLocalCombo ({
            fieldLabel: langs('Причина отказа'),
            hiddenName: 'DeclCause_id',
            displayField: 'DeclCause_Name',
            valueField: 'DeclCause_id',
            editable: true,
            width: 400,
            tpl: new Ext.XTemplate(
                '<tpl for="."><div class="x-combo-list-item">',
                '{DeclCause_Name}&nbsp;',
                '</div></tpl>'
            ),
            store: new Ext.data.SimpleStore({
                id: 0,
                fields: [
                    'DeclCause_id',
                    'DeclCause_Name'
                ],
                data: [
                    ['1', langs('Предоставлены неполные данные')],
                    ['2', langs('Данные неверны')],
                    ['3', langs('Копии документов нечитаемые')],
                    ['4', langs('Нет оснований для включения в льготную категорию граждан')]
                ]
            }),
            listeners: {
                change: function() {
                    wnd.setButtonsDisabled();
                },
                select: function() {
                    wnd.setButtonsDisabled();
                }
            },
            getSelectedRecordField: function(field_name) {
                var combo = this;
                var result = null;

                if (combo.getValue() > 0) {
                    var idx = combo.getStore().findBy(function(record) {
                        return (record.get(combo.valueField) == combo.getValue());
                    })
                    if (idx > -1) {
                        result = this.getStore().getAt(idx).get(field_name);
                    }
                }

                return result;
            }
        });

        var form = new Ext.Panel({
            autoScroll: true,
            bodyBorder: false,
            bodyStyle: 'padding: 5px 5px 0;',
            height: 70,
            border: false,
            frame: true,
            region: 'center',
            labelAlign: 'right',
            items: [{
                xtype: 'form',
                autoHeight: true,
                id: 'PersonPrivilegeReqExpertiseForm',
                bodyStyle: 'background: #DFE8F6; padding: 0px;',
                border: true,
                labelWidth: 215,
                labelAlign: 'right',
                collapsible: true,
                items: [{
                    xtype: 'hidden',
                    name: 'PersonPrivilegeReq_id'
                }, {
                    xtype: 'hidden',
                    name: 'DocumentPrivilege_id'
                }, {
                    xtype: 'fieldset',
                    title: langs('Запрос подан'),
                    autoHeight: true,
                    style: 'padding: 3px; margin-bottom: 7px; display: block;',
                    labelWidth: 210,
                    items: [this.CommonInformationPanel]
                },
                this.privilege_type_combo,
                {
                    xtype: 'swdiagcombo',
                    fieldLabel: 'Диагноз',
                    hiddenName: 'Diag_id',
                    width: 300,
                    disabled: true
                }, {
                    xtype: 'fieldset',
                    title: langs('Документ о праве на льготу'),
                    autoHeight: true,
                    style: 'padding: 3px; margin-bottom: 7px; display: block;',
                    labelWidth: 210,
                    items: [{
                        xtype: 'textfield',
                        fieldLabel: 'Вид документа',
                        name: 'DocumentPrivilegeType_Name',
                        width: 300,
                        disabled: true
                    }, {
                        xtype: 'textfield',
                        fieldLabel: 'Серия документа',
                        name: 'DocumentPrivilege_Ser',
                        width: 300,
                        disabled: true
                    }, {
                        xtype: 'textfield',
                        fieldLabel: 'Номер документа',
                        name: 'DocumentPrivilege_Num',
                        width: 300,
                        disabled: true
                    }, {
                        xtype: 'swdatefield',
                        format: 'd.m.Y',
                        plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
                        fieldLabel: 'Дата выдачи документа',
                        name: 'DocumentPrivilege_begDate',
                        disabled: true
                    }, {
                        xtype: 'textfield',
                        fieldLabel: 'Организация, выдавшая документ',
                        name: 'DocumentPrivilege_Org',
                        disabled: true,
                        width: 300
                    }]
                }, {
                    layout: 'column',
                    items: [{
                        layout: 'form',
                        items: [{
                            xtype: 'swdatefield',
                            format: 'd.m.Y',
                            plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
                            fieldLabel: 'Период действия льготы: c',
                            name: 'PersonPrivilegeReq_begDT',
                            labelSeparator: '',
                            disabled: true
                        }]
                    }, {
                        layout: 'form',
                        labelWidth: 25,
                        items: [{
                            xtype: 'swdatefield',
                            format: 'd.m.Y',
                            plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
                            fieldLabel: 'по',
                            name: 'PersonPrivilegeReq_endDT',
                            labelSeparator: '',
                            disabled: true,
                            listeners: {
                                change: function() {
                                    wnd.setButtonsDisabled();
                                }
                            }
                        }]
                    }]
                }, {
                    layout: 'form',
                    items: [{
						xtype: 'textfield',
						fieldLabel: 'Фамилия при рождении',
						name: 'PersonSurNameAtBirth_SurName',
						width: 300,
						disabled: true
                    }]
                }]
            }, {
                xtype: 'fieldset',
                title: langs('Передаваемые документы'),
                autoHeight: true,
                style: 'padding: 3px; margin-bottom: 2px; display: block;',
                items: [
                    wnd.FileUploadPanel
                ]
            }, {
                xtype: 'form',
                autoHeight: true,
                id: 'PersonPrivilegeReqResultExpertiseForm',
                url: '/?c=Privilege&m=savePersonPrivilegeReqExpertise',
                bodyStyle: 'background: #DFE8F6; padding: 0px; margin-top: 5px;',
                border: true,
                labelWidth: 215,
                labelAlign: 'right',
                collapsible: true,
                items: [
                    wnd.decl_cause_combo
                ]
            }]
        });

        Ext.apply(this, {
            layout: 'border',
            buttons: [{
                handler: function() {
                    this.ownerCt.doSave({action: 'insert'});
                },
                iconCls: 'save16',
                text: langs('Включить')
            },
            {
                handler: function() {
                    this.ownerCt.doSave({action: 'reject'});
                },
                iconCls: 'save16',
                text: langs('Отказать')
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
            }],
            items:[
                wnd.PersonPanel,
                form
            ]
        });
        sw.Promed.swPersonPrivilegeReqExpertiseWindow.superclass.initComponent.apply(this, arguments);
        this.form = this.findById('PersonPrivilegeReqExpertiseForm').getForm();
        this.res_form = this.findById('PersonPrivilegeReqResultExpertiseForm').getForm();
    }
});