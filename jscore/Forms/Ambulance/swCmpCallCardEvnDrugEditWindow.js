/**
 * swCmpCallCardEvnDrugEditWindow - окно редактирования информации об использовании медикаментов без учета остатков
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Ambulance
 * @access       public
 * @copyright    Copyright (c) 2017 Swan Ltd.
 * @author       Sysolin Maksim
 * @version      06.2017
 * @comment
 */
sw.Promed.swCmpCallCardEvnDrugEditWindow = Ext.extend(sw.Promed.BaseForm, {

    id: 'CmpCallCardEvnDrugEditWindow',
    autoHeight: false,
    layout: 'form',
    modal: true,
    resizable: false,
    width: 680,

    // имя основной формы
    formName: 'CmpCallCardEvnDrugEditForm',
    // краткое имя формы (для айдишников)
    formPrefix: 'CCCEDEW_',

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

    doSave:  function(saveAndContinue) {

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
            drug_combo = form.findField('DrugNomen_id'),
            drug_data = drug_combo.getSelectedRecordData();

        params.GoodsUnit_Name = form.findField('GoodsUnit_Name').getValue();
        params.DrugNomen_Name = !Ext.isEmpty(drug_data.DrugNomen_Name) ? drug_data.DrugNomen_Name : null;
        params.DrugNomen_Code = !Ext.isEmpty(drug_data.DrugNomen_Code) ? drug_data.DrugNomen_Code : null;

        wnd.onSave(params);

        if (!saveAndContinue)
            wnd.hide();
        else
            wnd.formPartiallyReset();

        return true;
    },

    formPartiallyReset: function (){

        var wnd = this,
            form = wnd.getMainForm(),
            resetFields = [
                'DrugNomen_id',
                'EvnDrug_Kolvo',
                'GoodsUnit_id',
                'GoodsUnit_Name',
                'EvnDrug_Comment'
            ];

        resetFields.forEach(function(fName){
            form.findField(fName).setValue('');
        });

        if (wnd.defaultSetDate) {
			form.findField('EvnDrug_setDate').setValue(wnd.defaultSetDate);
		}

		if (wnd.defaultSetTime) {
			form.findField('EvnDrug_setTime').setValue(wnd.defaultSetTime);
		}

        form.findField('DrugNomen_id').focus(true);
    },

    show: function() {

        sw.Promed.swCmpCallCardEvnDrugEditWindow.superclass.show.apply(this, arguments);

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

        this.setTitle("Расход медикаментов на пациента");

        for (var field_name in args) {
            //log(field_name +':'+ args[field_name]);
            wnd[field_name] = args[field_name];
        }

		wnd.defaultSetDate = null;
        if (args.EvnDrug_setDate) {
			wnd.defaultSetDate = args.EvnDrug_setDate;
		}

		wnd.defaultSetTime = null;
		if (args.EvnDrug_setTime) {
			wnd.defaultSetTime = args.EvnDrug_setTime;
		}

        form.setValues(args);
        loadMask.show();

        var goodsUnitField = form.findField('GoodsUnit_Name');

        switch (wnd.action) {
            case 'add':

                wnd.setTitle(this.title + ": Добавление");
                loadMask.hide();
                wnd.setDisabled(false);
                goodsUnitField.setDisabled(true);

                wnd.buttons[0].show();

				setTimeout(function() {
					wnd.formPartiallyReset();
				}, 100);

                break;

            case 'edit':
            case 'view':

                if (args.DrugNomen_id) {
                    form.findField('DrugNomen_id').setValueById(args.DrugNomen_id);
                }

                if (args.GoodsUnit_id) {
                    form.findField('GoodsUnit_id').setValue(args.GoodsUnit_id);
                    form.findField('GoodsUnit_Name').setValue(args.GoodsUnit_Name);
                }

                wnd.setTitle(this.title + (this.action == "edit" ? ": Редактирование" : ": Просмотр"));
                wnd.setDisabled(this.action == "view");

                goodsUnitField.setDisabled(true);
                wnd.buttons[0].hide();

                loadMask.hide();
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
                height: 430,
                frame: true,
                layout: 'form',
                id: formName,
                url:'/?c=EvnDrug&m=saveCmpCallCardEvnDrug',
                labelWidth: 120,
                labelAlign: 'right',

                items: [
                    {
                        xtype: 'hidden',
                        name: 'EvnDrug_id'
                    },{
                        xtype: 'hidden',
                        name: 'Lpu_id'
                    },{
                        xtype: 'hidden',
                        name: 'CmpCallCard_id'
                    }, {
                        xtype: 'hidden',
                        name: 'EmergencyTeam_id'
                    }, {
                        xtype: 'hidden',
                        name: 'LpuSection_id'
                    },{
                    layout: 'column',
                    items: [{

                        layout: 'form',
                        items: [{

                            xtype: 'swdatefield',
                            fieldLabel: 'Дата',
                            name: 'EvnDrug_setDate',
                            allowBlank: false
                        }]
                    }, {
                        layout: 'form',
                        labelWidth: 60,
                        items: [{

                            xtype: 'swtimefield',
                            fieldLabel: 'Время',
                            name: 'EvnDrug_setTime',
                            plugins: [new Ext.ux.InputTextMask('99:99', true)],
                            allowBlank: false,
                            width: 115
                        }]
                    }]
                },
                    {
                        xtype: 'swcustomownercombo',
                        fieldLabel: 'Наименование',
                        hiddenName: 'DrugNomen_id',
                        displayField: 'DrugNomen_Name',
                        valueField: 'DrugNomen_id',
                        width: 475,
                        allowBlank: false,
                        store: new Ext.data.SimpleStore({
                            autoLoad: false,
                            fields: [
                                { name: 'DrugNomen_id', mapping: 'DrugNomen_id' },
                                { name: 'DrugNomen_Name', mapping: 'DrugNomen_Name' },
                                { name: 'DrugNomen_Code', mapping: 'DrugNomen_Code' },
                                { name: 'GoodsUnit_id', mapping: 'GoodsUnit_id' },
                                { name: 'GoodsUnit_Name', mapping: 'GoodsUnit_Name' },
                                { name: 'Drug_id', mapping: 'Drug_id' }
                            ],
                            key: 'DrugNomen_id',
                            sortInfo: { field: 'DrugNomen_Name' },
                            url:'/?c=DrugNomen&m=loadDrugNomenCmpDrugUsageCombo'
                        }),
                        ownerWindow: wnd,
                        listeners: {
                            'change': function(combo) {
                                combo.setLinkedFieldValuesSec();
                            }
                        },
                        setValueById: function(id) {
                            var combo = this;
                            combo.getStore().baseParams[combo.valueField] = id;
                            combo.getStore().load({
                                callback: function(){
                                    combo.setValue(id);
                                }
                            });
                        },
                        setLinkedFieldValuesSec: function() {

                            var str_data = this.getSelectedRecordData(),
                                goodUnitsNameField = wnd[formName].getForm().findField('GoodsUnit_Name'),
                                goodUnitsIdField = wnd[formName].getForm().findField('GoodsUnit_id'),
                                drugField = wnd[formName].getForm().findField('Drug_id');

                            if (str_data.GoodsUnit_id > 0 && str_data.GoodsUnit_Name) {
                                goodUnitsNameField.setValue(str_data.GoodsUnit_Name);
                                goodUnitsIdField.setValue(str_data.GoodsUnit_id);
                            }

                            if (str_data.Drug_id > 0) {
                                drugField.setValue(str_data.Drug_id);
                            }
                        },
                        clearValue: function() {
                            sw.Promed.SwCustomOwnerCombo.superclass.clearValue.apply(this, arguments);

                            var goodUnitsNameField = wnd[formName].getForm().findField('GoodsUnit_Name'),
                                goodUnitsIdField = wnd[formName].getForm().findField('GoodsUnit_id');

                            goodUnitsNameField.setValue('');
                            goodUnitsIdField.setValue('');
                        }
                    },
                    {
                        xtype: 'hidden',
                        name: 'Drug_id'
                    },
                    {
                        xtype: 'hidden',
                        name: 'GoodsUnit_id'
                    },
                    {
                        layout: 'column',
                        items: [{

                            layout: 'form',
                            items: [{

                                xtype: 'numberfield',
                                fieldLabel: 'Количество',
                                name: 'EvnDrug_Kolvo',
                                allowBlank: false,
                                allowNegative: false,
								allowDecimals: false,
                                width: 105,
                                autoCreate: {tag: "input",  maxLength: "3", autocomplete: "off"},
                                maxValue: 100,
                                minValue: 0
                            }]
                        }, {
                            layout: 'form',
                            labelWidth: 60,
                            items: [
                               {
                                xtype: 'textfield',
                                fieldLabel: 'Ед. изм.',
                                name: 'GoodsUnit_Name',
                                allowBlank: false,
                                disabled:true,
                                width: 200,
                            }]
                            //items: [{
                            //    xtype: 'swgoodsunitcombo',
                            //    hiddenName: 'GoodsUnit_id',
                            //    displayField: 'GoodsUnit_Name',
                            //    valueField: 'GoodsUnit_id',
                            //    fieldLabel: 'Ед. изм.',
                            //    disabled: true,
								//typeAhead: false,
								//triggerAction: null,
								//editable: false,
								//enableKeyEvents: false,
								//selectOnFocus: false,
								//expand: Ext.emptyFn,
								//readOnly: true,
								//hideTrigger: true,
                            //    allowBlank: true,
                            //    anchor: null,
                            //    width: 150,
                            //    ownerWindow: wnd,
                            //    childrenList: [],
                            //    setValueById: function(id) {
                            //        var combo = this;
                            //        combo.getStore().baseParams[combo.valueField] = id;
                            //        combo.getStore().load({
                            //            callback: function(){
                            //                combo.setValue(id);
                            //            }
                            //        });
                            //    },
                            //}]
                        }]
                    },

                    {
                        xtype: 'textarea',
                        fieldLabel: 'Примечание',
                        name: 'EvnDrug_Comment',
                        allowNegative: false,
                        width: 475,
                        height: 100
                    },
                ]
        });

        Ext.apply(this, {
            buttons:
                [{
                    handler: function()
                    {
                        var saveAndContinue = true;
                        this.ownerCt.doSave(saveAndContinue);
                    },
                    iconCls: 'save16',
                    text: BTN_FRMSAVEANDCONTINUE,
                    hidden: true
                },
                    {
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
            items: [
                this[this.formName]
            ]
        });

        sw.Promed.swCmpCallCardEvnDrugEditWindow.superclass.initComponent.apply(this, arguments);
    }
});