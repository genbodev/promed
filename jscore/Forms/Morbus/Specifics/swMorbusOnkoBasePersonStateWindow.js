/**
* swMorbusOnkoBasePersonStateWindow - окно редактирования "Общее состояние пациента"
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      MorbusOnko
* @access       public
* @copyright    Copyright (c) 2013 Swan Ltd.
* @version      06.2013
* @comment      
*/

sw.Promed.swMorbusOnkoBasePersonStateWindow = Ext.extend(sw.Promed.BaseForm, {
    action: null,
    winTitle: lang['obschee_sostoyanie_patsienta'],
    buttonAlign: 'left',
    callback: Ext.emptyFn,
    closable: true,
    closeAction: 'hide',
    draggable: true,
    formMode: 'remote',
    isSave: false,
    formStatus: 'edit',
    error: false,
    layout: 'border',
    modal: true,
    width: 700,
    height: 400,
    maximizable: true,
    autoScroll: true,
    listeners: {
        hide: function() {
            this.onHide();
        }
    },
    onHide: Ext.emptyFn,
    doSave:  function() {
        var form = this.formPanel.getForm();
        this.isSave = true;
        this.checkMorbusParams(form.findField('MorbusOnkoBasePersonState_setDT').getValue(), form.findField('MorbusOnkoBase_id').getValue());
        return false;
    },
    submit: function() {
        var that = this;
        var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение..."});
        loadMask.show();
        var formParams = this.form.getValues();
        Ext.Ajax.request({
            failure:function () {
                loadMask.hide();
            },
            params: formParams,
            method: 'POST',
            success: function (result, action) {
                loadMask.hide();
                if (result.responseText) {
                    var response = Ext.util.JSON.decode(result.responseText);
                    formParams.MorbusOnkoBasePersonState_id = response.MorbusOnkoBasePersonState_id;
                    that.callback(formParams);
                    that.hide();
                }
            },
            url:'/?c=MorbusOnkoBasePersonState&m=save'
        });
    },
    setFieldsDisabled: function(d)
    {
        var form = this;
        this.form.items.each(function(f)
        {
            if (f && (f.xtype!='hidden') && (f.xtype!='fieldset')  && (f.changeDisabled!==false))
            {
                f.setDisabled(d);
            }
        });
        form.MorbusOnkoTumorStatusFrame.setReadOnly(d);
        form.buttons[0].setDisabled(d);
    },
    onLoadForm: function(formParams) {
        var accessType = formParams.accessType || 'edit';
        this.setFieldsDisabled(this.action == 'view' || accessType == 'view');

        this.form.setValues(formParams);

        var grid = this.MorbusOnkoTumorStatusFrame.getGrid();
        grid.getStore().removeAll();
        if (this.action != 'add') {
            grid.getStore().load({
                params: {MorbusOnkoBasePersonState_id: formParams.MorbusOnkoBasePersonState_id},
                globalFilters: {MorbusOnkoBasePersonState_id: formParams.MorbusOnkoBasePersonState_id}
            });
        }
    },
    openMorbusOnkoTumorStatusWindow: function(action) {
        if (!action || !action.toString().inlist(['edit', 'view'])) {
            return false;
        }

        if (getWnd('swMorbusOnkoTumorStatusWindow').isVisible()) {
            getWnd('swMorbusOnkoTumorStatusWindow').hide();
        }

        var grid = this.MorbusOnkoTumorStatusFrame.getGrid();
        var selected_record = grid.getSelectionModel().getSelected();
        if (!selected_record) {
            return false;
        }

        var params = {};
        params.action = action;
        params.callback = function(data) {
            if (!data) {
                return false;
            }
            // Обновить запись в grid
            selected_record.set('OnkoTumorStatusType_id', data['OnkoTumorStatusType_id'] || null);
            selected_record.set('OnkoTumorStatusType_Name', data['OnkoTumorStatusType_Name'] || '');
            selected_record.commit();
            return true;
        };
        params.formParams = selected_record.data;
        params.onHide = function() {
            grid.getView().focusRow(grid.getStore().indexOf(selected_record));
        };

        getWnd('swMorbusOnkoTumorStatusWindow').show(params);

    },
    show: function() {
        var that = this;
        sw.Promed.swMorbusOnkoBasePersonStateWindow.superclass.show.apply(this, arguments);
        this.action = 'add';
        this.callback = Ext.emptyFn;
        if ( !arguments[0] || !arguments[0].formParams || !arguments[0].formParams.Person_id) {
            sw.swMsg.alert(lang['oshibka'], lang['ne_ukazanyi_vhodnyie_dannyie'], function() { that.hide(); });
            return false;
        }
        this.Person_id = arguments[0].formParams.Person_id;
        if ( arguments[0].action ) {
            this.action = arguments[0].action;
        }
        if ( arguments[0].callback && typeof arguments[0].callback == 'function' ) {
            this.callback = arguments[0].callback;
        }
        this.form.reset();

        switch (arguments[0].action) {
            case 'add':
                this.setTitle(this.winTitle +lang['_dobavlenie']);
                this.onLoadForm(arguments[0].formParams);
                break;
            case 'edit':
                this.setTitle(this.winTitle +lang['_redaktirovanie']);
                break;
            case 'view':
                this.setTitle(this.winTitle +lang['_prosmotr']);
                break;
        }

        var loadMask = new Ext.LoadMask(this.form.getEl(), {msg:lang['zagruzka']});
        loadMask.show();
        switch (this.action) {
            case 'add':
                that.InformationPanel.load({
                    Person_id: this.Person_id
                });
                loadMask.hide();
                break;
            case 'edit':
            case 'view':
                Ext.Ajax.request({
                    failure:function () {
                        sw.swMsg.alert(lang['oshibka'], lang['ne_udalos_poluchit_dannyie_s_servera']);
                        loadMask.hide();
                        that.hide();
                    },
                    params:{
                        MorbusOnkoBasePersonState_id: arguments[0].formParams.MorbusOnkoBasePersonState_id
                    },
                    method: 'POST',
                    success: function (response) {
                        var result = Ext.util.JSON.decode(response.responseText);
                        if (!result[0]) { return false; }

                        that.onLoadForm(result[0]);

                        that.InformationPanel.load({
                            Person_id: that.Person_id
                        });
                        loadMask.hide();
                        return true;
                    },
                    url:'/?c=MorbusOnkoBasePersonState&m=load'
                });
                break;
        }
        return true;
    },
    checkMorbusParams: function(newValue, MorbusOnkoBase_id) {
        var _this = this,
            loadMask = new Ext.LoadMask(_this.getEl(), {msg:lang['zagruzka_spiska_sostoyaniy_opuholevogo_protsessa']}),
            grid = _this.MorbusOnkoTumorStatusFrame.getGrid();
        loadMask.show();

        if (this.action == 'edit' && _this.isSave) {
            _this.submit();
            return true;
        };
        Ext.Ajax.request({
            failure:function () {
                sw.swMsg.alert(lang['oshibka'], lang['ne_udalos_poluchit_dannyie_s_servera']);
                loadMask.hide();
            },
            params: {
                MorbusOnkoBasePersonState_setDT: Ext.util.Format.date(newValue, 'd.m.Y'),
                MorbusOnkoBase_id: MorbusOnkoBase_id
            },
            method: 'POST',
            success: function (response) {
                var result = Ext.util.JSON.decode(response.responseText);
                    loadMask.hide();
                if(result.Error_Msg&&result.Error_Msg!=''){
                    sw.swMsg.alert(lang['oshibka'], result.Error_Msg + lang['sohranenie_nevozmojno']);
                    _this.error = result.Error_Msg;
                    return false;
                } else {
                    _this.error = null;
                    _this.form.findField('MorbusOnkoBasePersonState_id').setValue(result.MorbusOnkoBasePersonState_id);
                    _this.action = 'edit';
                    _this.setTitle(_this.winTitle +lang['_redaktirovanie']);
                    grid.getStore().load({
                        params: {MorbusOnkoBasePersonState_id: result.MorbusOnkoBasePersonState_id},
                        globalFilters: {MorbusOnkoBasePersonState_id: result.MorbusOnkoBasePersonState_id}
                    });

                    if (Ext.isEmpty(_this.error) && _this.isSave){
                        if ( !_this.formPanel.getForm().isValid() ) {
                            sw.swMsg.show(
                                {
                                    buttons: Ext.Msg.OK,
                                    fn: function()
                                    {
                                        _this.findById('MorbusOnkoBasePersonStateEditForm').getFirstInvalidEl().focus(true);
                                    },
                                    icon: Ext.Msg.WARNING,
                                    msg: ERR_INVFIELDS_MSG,
                                    title: ERR_INVFIELDS_TIT
                                });
                            return false;
                        } else {
                        _this.submit();
                        return true;
                        }
                    }
                }
            },
            url:'/?c=MorbusOnkoBasePersonState&m=create'
        });
    },
    initComponent: function() {
        var that = this;

        this.InformationPanel = new sw.Promed.PersonInformationPanelShort({
            region: 'north'
        });

        this.MorbusOnkoTumorStatusFrame = new sw.Promed.ViewFrame({
            title: getRegionNick() == 'kz'?'Iсiк процесiнiң жағдайы (Состояние опухолевого процесса)':lang['2_sostoyanie_opuholevogo_protsessa'],
            actions: [
                {name: 'action_add', hidden: true, disabled: true},
                {name: 'action_edit', handler: function() {
                    that.openMorbusOnkoTumorStatusWindow('edit');
                }},
                {name: 'action_view', handler: function() {
                    that.openMorbusOnkoTumorStatusWindow('view');
                }},
                {name: 'action_delete', hidden: true, disabled: true},
                {name: 'action_print'}
            ],
            autoExpandColumn: 'autoexpand',
            autoExpandMin: 150,
            autoLoadData: false,
            border: true,
            dataUrl: '/?c=MorbusOnkoTumorStatus&m=readList',
            collapsible: false,
            id: 'MHCW_MorbusOnkoTumorStatus',
            paging: false,
            stringfields: [
                {name: 'MorbusOnkoTumorStatus_id', type: 'int', header: 'ID', key: true},
                {name: 'MorbusOnkoBasePersonState_id', type: 'int', hidden: true},
                {name: 'Diag_id', type: 'int', hidden: true},
                {name: 'OnkoTumorStatusType_id', type: 'int', hidden: true},
                {name: 'MorbusOnkoTumorStatus_NumTumor', type: 'string', header: lang['№_opuholi'], width: 100},
                {name: 'Diag_Name', type: 'string', header: lang['topografiya'], width: 240},
                {name: 'OnkoTumorStatusType_Name', type: 'string', header: lang['sostoyanie'], id: 'autoexpand'}
            ],
            toolbar: true
        });

        this.formPanel = new Ext.form.FormPanel({
            title: lang['1_sostoyanie_patsienta'],
            autoHeight: true,
            bodyBorder: false,
            bodyStyle:'background:#DFE8F6;padding:5px;',
            labelWidth: 200,
            labelAlign: 'right',
            border: false,
            frame: false,
            id: 'MorbusOnkoBasePersonStateEditForm',
            items: [{
                name: 'MorbusOnkoBasePersonState_id',
                xtype: 'hidden',
                value: 0
            }, {
                name: 'MorbusOnkoBase_id',
                xtype: 'hidden',
                value: 0
            }, {
                fieldLabel: lang['data_nablyudeniya'],
                name: 'MorbusOnkoBasePersonState_setDT',
                xtype: 'swdatefield',
                allowBlank: false,
                plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
                listeners: {
                    'change':function (field, newValue, oldValue) {
                        var grid = that.MorbusOnkoTumorStatusFrame.getGrid(),
                            MorbusOnkoBase_id = that.form.findField('MorbusOnkoBase_id').getValue(),
                            MorbusOnkoBasePersonState_id = that.form.findField('MorbusOnkoBasePersonState_id').getValue();
                        that.isSave = false;
                        if (
                            newValue
                            && !oldValue
                            && !MorbusOnkoBasePersonState_id
                            && that.action == 'add'
                            && grid.getStore().getCount() == 0
                        ) {
                            that.checkMorbusParams(newValue, MorbusOnkoBase_id);
                        }
                    }
                }
                // При открытии формы на добавление список обновлять после проставления значения в поле «Дата наблюдения»
                // (первоначально список пустой).
            }, {
                fieldLabel: getRegionNick() == 'kz' ? 'Пациенттiң жалпы жағдайы (Общее состояние пациента)' :lang['obschee_sostoyanie_patsienta'],
                allowBlank: false,
                hiddenName: 'OnkoPersonStateType_id',
                xtype: 'swcommonsprlikecombo',
                sortField:'OnkoPersonStateType_Code',
                comboSubject: 'OnkoPersonStateType',
                width: 400
            }],
            url:'/?c=MorbusOnkoBasePersonState&m=save',
            reader: new Ext.data.JsonReader({
                success: Ext.emptyFn
            }, [
                {name: 'MorbusOnkoBasePersonState_id'},
                {name: 'MorbusOnkoBase_id'},
                {name: 'MorbusOnkoBasePersonState_setDT'},
                {name: 'OnkoPersonStateType_id'}
            ])
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
                    HelpButton(this),
                    {
                        handler: function()
                        {
                            this.ownerCt.hide();
                        },
                        iconCls: 'cancel16',
                        text: BTN_FRMCANCEL
                    }],
            items: [
                this.InformationPanel,
                {
                    region: 'center',
                    layout: 'fit',
                    items: [
                        this.formPanel,
                        this.MorbusOnkoTumorStatusFrame
                    ]
                }]
        });
        sw.Promed.swMorbusOnkoBasePersonStateWindow.superclass.initComponent.apply(this, arguments);
        this.form = this.formPanel.getForm();
    }
});