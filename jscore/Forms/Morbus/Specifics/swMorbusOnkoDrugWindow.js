/**
* swMorbusOnkoDrugWindow - окно редактирования "Препарат"
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      MorbusOnko
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @version      06.2013
* @comment      
*/

sw.Promed.swMorbusOnkoDrugWindow = Ext.extend(sw.Promed.BaseForm, {
    action: null,
    winTitle: langs('Препарат'),
    buttonAlign: 'left',
    callback: Ext.emptyFn,
    closable: true,
    closeAction: 'hide',
    draggable: true,
    formMode: 'remote',
    formStatus: 'edit',
    layout: 'form',
    modal: true,
    minWidth: 600,
    width: 600,
    autoHeight: true,
    maximizable: false,
    listeners: {
        hide: function() {
            this.onHide();
        }
    },
    onHide: Ext.emptyFn,
    doSave:  function() {
        var that = this;

        if ( !this.form.isValid() )
        {
            sw.swMsg.show(
                {
                    buttons: Ext.Msg.OK,
                    fn: function()
                    {
                        that.findById('MorbusOnkoDrugEditForm').getFirstInvalidEl().focus(true);
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
                    if (response.success) {
                        formParams.MorbusOnkoDrug_id = response.MorbusOnkoDrug_id;
                        that.callback(formParams);
                        that.hide();
                    }
                }
            },
            url:'/?c=MorbusOnkoDrug&m=save'
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
        form.buttons[0].setDisabled(d);
    },
    onLoadForm: function(formParams) {
        var accessType = formParams.accessType || 'edit';
        this.setFieldsDisabled(this.action == 'view' || accessType == 'view');

        this.form.setValues(formParams);

		this.form.findField('DrugDictType_id').fireEvent('change', this.form.findField('DrugDictType_id'), this.form.findField('DrugDictType_id').getValue());

        if (!Ext.isEmpty(formParams.Evn_id)) {
            this.drug_combo.getStore().baseParams.Evn_id = formParams.Evn_id;
        }
        if (!Ext.isEmpty(formParams.MorbusOnkoDrug_endDT)) {
            this.drug_combo.getStore().baseParams.Date = formParams.MorbusOnkoDrug_endDT;
        }
        if (!Ext.isEmpty(formParams.DrugMNN_id)) {
            this.drug_combo.setValueById(formParams.DrugMNN_id);
        }
    },
    show: function() {
        var that = this;
        sw.Promed.swMorbusOnkoDrugWindow.superclass.show.apply(this, arguments);
        this.action = 'add';
        this.callback = Ext.emptyFn;
        if ( !arguments[0] || !arguments[0].formParams) {
            sw.swMsg.alert(langs('Ошибка'), langs('Не указаны входные данные'), function() { that.hide(); });
            return false;
        }
        if ( arguments[0].action ) {
            this.action = arguments[0].action;
        }
        if ( arguments[0].callback && typeof arguments[0].callback == 'function' ) {
            this.callback = arguments[0].callback;
        }
        this.form.reset();
        this.drug_combo.fullReset();

        this.EvnUsluga_setDT = arguments[0].EvnUsluga_setDT || null;
        this.EvnUsluga_disDT = arguments[0].EvnUsluga_disDT || null;
        var set_dt_field = this.form.findField('MorbusOnkoDrug_begDT');
        var dis_dt_field = this.form.findField('MorbusOnkoDrug_endDT');
        set_dt_field.setMinValue(this.EvnUsluga_setDT);
        set_dt_field.setMaxValue(this.EvnUsluga_disDT);
        dis_dt_field.setMinValue(this.EvnUsluga_setDT);
        dis_dt_field.setMaxValue(this.EvnUsluga_disDT);

        switch (arguments[0].action) {
            case 'add':
                this.setTitle(this.winTitle +': ' + FRM_ACTION_ADD);
                break;
            case 'edit':
                this.setTitle(this.winTitle +': ' + FRM_ACTION_EDIT);
                break;
            case 'view':
                this.setTitle(this.winTitle +': ' + FRM_ACTION_VIEW);
                break;
        }

        var loadMask = new Ext.LoadMask(this.form.getEl(), {msg:langs('Загрузка...')});
        loadMask.show();

		// https://redmine.swan.perm.ru/issues/64061
		// Только для Уфы, для остальных регионов поле невидимо
		// this.form.findField('DrugDictType_id').setContainerVisible(getRegionNick().inlist([ 'ufa' ]));

		switch (this.action) {
            case 'add':
                loadMask.hide();
				this.form.findField('DrugDictType_id').setFieldValue('DrugDictType_Code', 1); // РЛС (по умолчанию)
				this.onLoadForm(arguments[0].formParams);
                break;
            case 'edit':
            case 'view':
                Ext.Ajax.request({
                    failure:function () {
                        sw.swMsg.alert(langs('Ошибка'), langs('Не удалось получить данные с сервера'));
                        loadMask.hide();
                        that.hide();
                    },
                    params:{
                        MorbusOnkoDrug_id: arguments[0].formParams.MorbusOnkoDrug_id
                    },
                    method: 'POST',
                    success: function (response) {
                        loadMask.hide();
                        var result = Ext.util.JSON.decode(response.responseText);
                        if (!result[0]) { return false; }
                        if (result[0]['Error_Msg']) {
                            sw.swMsg.alert(langs('Ошибка'), result[0]['Error_Msg']);
                            return false;
                        }
                        that.onLoadForm(result[0]);
                        return true;
                    },
                    url:'/?c=MorbusOnkoDrug&m=load'
                });
                break;
        }
        return true;
    },
    initComponent: function() {
        var that = this;

        this.drug_combo = new sw.Promed.SwCustomRemoteCombo({
            displayField: 'DrugMNN_Name',
            fieldLabel: langs('Медикамент'),
            hiddenName: 'DrugMNN_id',
            listWidth: 400,
            store: new Ext.data.SimpleStore({
                autoLoad: false,
                fields: [
                    { name: 'DrugMNN_id', mapping: 'DrugMNN_id' },
                    { name: 'DrugMNN_Code', mapping: 'DrugMNN_Code' },
                    { name: 'DrugMNN_Name', mapping: 'DrugMNN_Name' }
                ],
                key: 'DrugMNN_id',
                sortInfo: { field: 'DrugMNN_Name' },
                url:'/?c=MorbusOnkoDrug&m=loadFedDrugMNNCombo'
            }),
            tpl: new Ext.XTemplate(
                '<tpl for="."><div class="x-combo-list-item">',
                '<table style="width:100%;border: 0;"><td style="width:80%;"><font color="red">{DrugMNN_Code}</font>&nbsp;{DrugMNN_Name}</td><td style="width:20%;"></td></tr></table>',
                '</div></tpl>'
            ),
            valueField: 'DrugMNN_id',
            width: 330
        });

        this.formPanel = new Ext.form.FormPanel({
            autoHeight: true,
            autoScroll: true,
            bodyBorder: false,
            border: false,
            frame: false,
            id: 'MorbusOnkoDrugEditForm',
            bodyStyle:'background:#DFE8F6;padding:5px;',
            labelWidth: 200,
            labelAlign: 'right',
            region: 'center',
            items: [{
                name: 'MorbusOnkoDrug_id',
                xtype: 'hidden',
                value: 0
            }, {
                name: 'MorbusOnko_id',
                xtype: 'hidden',
                value: 0
            }, {
                name: 'Evn_id',
                xtype: 'hidden',
                value: 0
            },  {
                name: 'MorbusOnkoVizitPLDop_id',
                xtype: 'hidden',
                value: 0
            },  {
                name: 'MorbusOnkoDiagPLStom_id',
                xtype: 'hidden',
                value: 0
            },  {
                name: 'MorbusOnkoLeave_id',
                xtype: 'hidden',
                value: 0
            }, {
                fieldLabel: langs('Дата начала'),
                name: 'MorbusOnkoDrug_begDT',
                xtype: 'swdatefield',
                allowBlank: false,
                plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
                listeners: {
                    'change':function (field, newValue, oldValue) {
                        var dis_dt_field = that.form.findField('MorbusOnkoDrug_endDT');
                        if (newValue) {
                            dis_dt_field.setMinValue(newValue);
                        }
                        else {
                            dis_dt_field.setMinValue(null);
                        }
                    }
                }
            }, {
                fieldLabel: langs('Дата окончания'),
                name: 'MorbusOnkoDrug_endDT',
                xtype: 'swdatefield',
                plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
                listeners: {
                    'change':function (field, newValue, oldValue) {
                        var date_str = null;
                        if (!Ext.isEmpty(newValue)) {
                            date_str = newValue.format('d.m.Y');
                        }
                        that.drug_combo.getStore().baseParams.Date = date_str;
                        that.drug_combo.clearValue();
                        delete that.drug_combo.lastQuery;
                    }
                }
            }, {
                allowBlank: false,
                comboSubject: 'DrugDictType',
                fieldLabel: langs('Справочник'),
                hiddenName: 'DrugDictType_id',
				listeners: {
					'change': function(combo, newValue, oldValue) {
						var index = combo.getStore().findBy(function(rec) {
							return (rec.get(combo.valueField) == newValue);
						});
						combo.fireEvent('select', combo, combo.getStore().getAt(index), index);
					},
					'select': function(combo, record, idx) {
						var base_form = that.formPanel.getForm();

						base_form.findField('CLSATC_id').setContainerVisible(false);
                        base_form.findField('DrugMNN_id').setAllowBlank(true);
						base_form.findField('DrugMNN_id').setContainerVisible(false);
						base_form.findField('OnkoDrug_id').setAllowBlank(true);
						base_form.findField('OnkoDrug_id').setContainerVisible(false);

						if ( typeof record == 'object' && !Ext.isEmpty(record.get(combo.valueField)) ) {
							switch ( parseInt(record.get('DrugDictType_Code')) ) {
								case 1: // РЛС
									base_form.findField('CLSATC_id').setContainerVisible(true);
                                    base_form.findField('DrugMNN_id').setAllowBlank(false);
									base_form.findField('DrugMNN_id').setContainerVisible(true);

									base_form.findField('OnkoDrug_id').clearValue();
								break;

								case 2: // Кодификатор №9
									base_form.findField('OnkoDrug_id').setAllowBlank(false);
									base_form.findField('OnkoDrug_id').setContainerVisible(true);

									base_form.findField('CLSATC_id').clearValue();
									base_form.findField('DrugMNN_id').clearValue();
								break;
							}
						}
						else {
							base_form.findField('CLSATC_id').clearValue();
							base_form.findField('DrugMNN_id').clearValue();
							base_form.findField('OnkoDrug_id').clearValue();
						}

						that.syncShadow();
						that.syncSize();
					}
				},
                listWidth: 400,
                width: 330,
                xtype: 'swcommonsprcombo'
            }, {
                fieldLabel: langs('Препарат'),
                hiddenName: 'OnkoDrug_id',
                xtype: 'swonkodrugcombo',
                listWidth: 400,
                width: 330
            }, {
				anchor: null,
				ctxSerach: true,
                fieldLabel: langs('Препарат'),
                hiddenName: 'CLSATC_id',
                xtype: 'swrlsclsatccombo',
                listWidth: 400,
                width: 330
            },
            this.drug_combo,
            {
                fieldLabel: langs('Доза разовая'),
                name: 'MorbusOnkoDrug_Dose',
                xtype: 'textfield',
                width: 200
            }, {
                fieldLabel: langs('ед'),
                hiddenName: 'OnkoDrugUnitType_id',
                xtype: 'swcommonsprlikecombo',
                sortField:'OnkoDrugUnitType_Code',
                comboSubject: 'OnkoDrugUnitType',
                width: 200
            }, {
                fieldLabel: langs('Кратность'),
                name: 'MorbusOnkoDrug_Multi',
                regex: /^[1-9][0-9]*$/,
                invalidText: 'Поле должно быть целым, положительным числом.',
                xtype: 'textfield',
                width: 200
            }, {
                fieldLabel: langs('Периодичность (дней)'),
                name: 'MorbusOnkoDrug_Period',
                invalidText: 'Поле должно быть целым, положительным числом.',
                regex: /^[1-9][0-9]*$/,
                xtype: 'textfield',
                width: 200
            }, {
                fieldLabel: langs('Суммарная доза'),
                name: 'MorbusOnkoDrug_SumDose',
                xtype: 'textfield',
                width: 200
            }, {
                fieldLabel: langs('Метод введения'),
                name: 'MorbusOnkoDrug_Method',
                xtype: 'textfield',
                width: 200
            }, {
                fieldLabel: langs('Проведена профилактика тошноты и рвотного рефлекса'),
                hiddenName: 'MorbusOnkoDrug_IsPreventionVomiting',
                width: 100,
                xtype: 'swyesnocombo'
            }],
            url:'/?c=MorbusOnkoDrug&m=save',
            reader: new Ext.data.JsonReader({
                success: Ext.emptyFn
            }, [
                {name: 'MorbusOnkoDrug_id'},
                {name: 'MorbusOnko_id'},
                {name: 'MorbusOnkoDrug_begDT'},
                {name: 'MorbusOnkoDrug_endDT'},
                {name: 'DrugDictType_id'},
                {name: 'CLSATC_id'},
                {name: 'DrugMNN_id'},
                {name: 'OnkoDrug_id'},
                {name: 'OnkoDrugUnitType_id'},
                {name: 'MorbusOnkoDrug_Dose'},
                {name: 'MorbusOnkoDrug_Multi'},
                {name: 'MorbusOnkoDrug_Period'},
                {name: 'MorbusOnkoDrug_SumDose'},
                {name: 'MorbusOnkoDrug_Method'},
                {name: 'MorbusOnkoDrug_IsPreventionVomiting'},
                {name: 'Evn_id'},
                {name: 'MorbusOnkoVizitPLDop_id'},
                {name: 'MorbusOnkoDiagPLStom_id'},
                {name: 'MorbusOnkoLeave_id'}
            ])
        });
        Ext.apply(this, {
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
            items:[this.formPanel]
        });

        sw.Promed.swMorbusOnkoDrugWindow.superclass.initComponent.apply(this, arguments);

        this.form = this.formPanel.getForm();
	}	
});