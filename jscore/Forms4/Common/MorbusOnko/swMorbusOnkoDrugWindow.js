/**
* swMorbusOnkoDrugWindow - окно редактирования "Препарат"
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      MorbusOnko
* @access       public
* @copyright    Copyright (c) 2019 Swan Ltd.
* @comment      
*/

Ext6.define('common.MorbusOnko.swMorbusOnkoDrugWindow', {
	/* свойства */
	alias: 'widget.swMorbusOnkoDrugWindow',
    autoShow: false,
	closable: true,
	cls: 'arm-window-new emkd',
	constrain: true,
	extend: 'base.BaseForm',
	findWindow: false,
    header: true,
	modal: true,
	layout: 'form',
	refId: 'MorbusOnkoDrugsw',
	renderTo: Ext.getCmp('main-center-panel').body.dom,
	resizable: false,
	title: langs('Препарат'),
	winTitle: langs('Препарат'),
	width: 700,

	/* методы */
	save: function (options) {

        var win = this;
        if ( !this.form.isValid() )
        {
            sw.swMsg.show(
                {
                    buttons: Ext.Msg.OK,
                    fn: function()
                    {
                        win.findById('MorbusOnkoDrugEditForm').getFirstInvalidEl().focus(true);
                    },
                    icon: Ext.Msg.WARNING,
                    msg: ERR_INVFIELDS_MSG,
                    title: ERR_INVFIELDS_TIT
                });
            return false;
        }
		
        win.mask(LOAD_WAIT_SAVE);
		
        var formParams = this.form.getValues();
        Ext.Ajax.request({
            failure:function () {
                win.unmask();
            },
            params: formParams,
            method: 'POST',
            success: function (result, action) {
                win.unmask();
                if (result.responseText) {
                    var response = Ext.util.JSON.decode(result.responseText);
                    if (response.success) {
                        formParams.MorbusOnkoDrug_id = response.MorbusOnkoDrug_id;
                        win.callback(formParams);
                        win.hide();
                    }
                }
            },
            url:'/?c=MorbusOnkoDrug&m=save'
        });
	},
	
    setFieldsDisabled: function(d)
    {
        var form = this;
        this.FormPanel.items.each(function(f){
            if (f && (f.xtype!='hidden') && (f.xtype!='fieldset')  && (f.changeDisabled!==false)) {
                f.setDisabled(d);
            }
        });
		//form.MorbusOnkoTumorStatusFrame.setReadOnly(d);
        //form.buttons[0].setDisabled(d);
    },

    onLoadForm: function(formParams) {
        var accessType = formParams.accessType || 'edit';
        this.setFieldsDisabled(this.action == 'view' || accessType == 'view');

        this.form.setValues(formParams);

		this.form.findField('DrugDictType_id').fireEvent('change', this.form.findField('DrugDictType_id'), this.form.findField('DrugDictType_id').getValue());

        if (!Ext.isEmpty(formParams.Evn_id)) {
            this.drug_combo.getStore().proxy.extraParams.Evn_id = formParams.Evn_id;
        }
        if (!Ext.isEmpty(formParams.MorbusOnkoDrug_endDT)) {
            this.drug_combo.getStore().proxy.extraParams.Date = formParams.MorbusOnkoDrug_endDT;
        }
        if (!Ext.isEmpty(formParams.DrugMNN_id)) {
            this.drug_combo.setValueById(formParams.DrugMNN_id);
        }
    },
	
	onSprLoad: function(arguments) {
		
        var win = this;
        this.action = 'add';
        this.callback = Ext.emptyFn;
        if ( !arguments[0] || !arguments[0].formParams) {
            sw.swMsg.alert(langs('Ошибка'), langs('Не указаны входные данные'), function() { win.hide(); });
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

        win.mask(LOAD_WAIT);

		switch (this.action) {
            case 'add':
				win.unmask();
				this.form.findField('DrugDictType_id').setFieldValue('DrugDictType_Code', 1); // РЛС (по умолчанию)
				this.onLoadForm(arguments[0].formParams);
                break;
            case 'edit':
            case 'view':
                Ext.Ajax.request({
                    failure:function () {
                        sw.swMsg.alert(langs('Ошибка'), langs('Не удалось получить данные с сервера'));
                        win.unmask();
                        win.hide();
                    },
                    params:{
                        MorbusOnkoDrug_id: arguments[0].formParams.MorbusOnkoDrug_id
                    },
                    method: 'POST',
                    success: function (response) {
                        win.unmask();
                        var result = Ext.util.JSON.decode(response.responseText);
                        if (!result[0]) { return false; }
                        if (result[0]['Error_Msg']) {
                            sw.swMsg.alert(langs('Ошибка'), result[0]['Error_Msg']);
                            return false;
                        }
                        win.onLoadForm(result[0]);
                        return true;
                    },
                    url:'/?c=MorbusOnkoDrug&m=load'
                });
                break;
        }
	},

	show: function() {
		this.callParent(arguments);
	},

	/* конструктор */
    initComponent: function() {
        var win = this;

		Ext6.define(win.id + '_FormModel', {
			extend: 'Ext6.data.Model'
		});

        win.drug_combo = Ext6.create('swBaseCombobox', {
            displayField: 'DrugMNN_Name',
            fieldLabel: langs('Медикамент'),
            name: 'DrugMNN_id',
            listWidth: 800,
            store: Ext6.create('Ext6.data.Store', {
				fields: [
                    { name: 'DrugMNN_id', mapping: 'DrugMNN_id' },
                    { name: 'DrugMNN_Code', mapping: 'DrugMNN_Code' },
                    { name: 'DrugMNN_Name', mapping: 'DrugMNN_Name' }
				],
				autoLoad: false,
				sorters: {
					property: 'DrugMNN_Code',
					direction: 'ASC'
				},
				proxy: {
					type: 'ajax',
					actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
					url: '/?c=MorbusOnkoDrug&m=loadFedDrugMNNCombo',
					reader: {
						type: 'json'
					}
				},
				mode: 'remote'
			}),
			setValueById: function(id) {
				var combo = this;
				combo.getStore().proxy.extraParams[combo.valueField] = id;
				combo.getStore().load({
					callback: function(){
						combo.setValue(id);
						combo.getStore().proxy.extraParams[combo.valueField] = null;
					}
				});
			},
			fullReset: function() {
				this.reset();
				this.getStore().proxy.extraParams = new Object();
			},
			tpl: new Ext6.XTemplate(
				'<tpl for="."><div class="x6-boundlist-item">',
				'<table style="border: 3px; bordercolor: black; width: 100%;">',
				'<tr><td style="width: 20px; vertical-align: top;">{DrugMNN_Code}.&nbsp;</td>',
				'<td>{DrugMNN_Name}&nbsp;</td>',
				'</tr></table>',
				'</div></tpl>'
			),
            valueField: 'DrugMNN_id',
            width: 600
        });

		win.FormPanel = new Ext6.form.FormPanel({
			autoScroll: true,
			border: false,
			cls: 'emk_forms accordion-panel-window',
			bodyPadding: '15 25 15 37',
			defaults: {
				labelAlign: 'left',
				labelWidth: 200
			},
			reader: Ext6.create('Ext6.data.reader.Json', {
				type: 'json',
				model: win.id + '_FormModel'
			}),
			url: '/?c=MorbusOnkoDrug&m=save',
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
                xtype: 'datefield',
                allowBlank: false,
                listeners: {
                    'change':function (field, newValue, oldValue) {
                        var dis_dt_field = win.form.findField('MorbusOnkoDrug_endDT');
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
                xtype: 'datefield',
                listeners: {
                    'change':function (field, newValue, oldValue) {
                        var date_str = null;
                        if (!Ext.isEmpty(newValue)) {
                            date_str = newValue.format('d.m.Y');
                        }
                        win.drug_combo.getStore().proxy.extraParams.Date = date_str;
                        win.drug_combo.clearValue();
                    }
                }
            }, {
                allowBlank: false,
                comboSubject: 'DrugDictType',
                fieldLabel: langs('Справочник'),
                name: 'DrugDictType_id',
				listeners: {
					'change': function(combo, newValue, oldValue) {
						var index = combo.getStore().findBy(function(rec) {
							return (rec.get(combo.valueField) == newValue);
						});
						combo.fireEvent('select', combo, combo.getStore().getAt(index), index);
					},
					'select': function(combo, record, idx) {
						var base_form = win.FormPanel.getForm();

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
					}
				},
                listWidth: 400,
                width: 600,
                xtype: 'commonSprCombo'
            }, {
                fieldLabel: langs('Препарат'),
                name: 'OnkoDrug_id',
                comboSubject: 'OnkoDrug',
                listWidth: 400,
                width: 600,
                xtype: 'commonSprCombo'
            }, {
				anchor: null,
				ctxSerach: true,
                fieldLabel: langs('Препарат'),
                name: 'CLSATC_id',
                comboSubject: 'Clsatc',
				displayField: 'RlsClsatc_Name',
				valueField: 'RlsClsatc_id',
				displayCode: false,
				prefix: 'rls_',
                listWidth: 400,
                width: 600,
                xtype: 'commonSprCombo'
            },
            this.drug_combo,
            {
                fieldLabel: langs('Доза разовая'),
                name: 'MorbusOnkoDrug_Dose',
                xtype: 'textfield',
                width: 400
            }, {
                fieldLabel: langs('ед'),
                name: 'OnkoDrugUnitType_id',
                xtype: 'commonSprCombo',
                sortField:'OnkoDrugUnitType_Code',
                comboSubject: 'OnkoDrugUnitType',
                width: 400
            }, {
                fieldLabel: langs('Кратность'),
                name: 'MorbusOnkoDrug_Multi',
                xtype: 'textfield',
                width: 400
            }, {
                fieldLabel: langs('Периодичность'),
                name: 'MorbusOnkoDrug_Period',
                xtype: 'textfield',
                width: 400
            }, {
                fieldLabel: langs('Суммарная доза'),
                name: 'MorbusOnkoDrug_SumDose',
                xtype: 'textfield',
                width: 400
            }, {
                fieldLabel: langs('Метод введения'),
                name: 'MorbusOnkoDrug_Method',
                xtype: 'textfield',
                width: 400
            }, {
                fieldLabel: langs('Проведена профилактика тошноты и рвотного рефлекса'),
                name: 'MorbusOnkoDrug_IsPreventionVomiting',
                comboSubject: 'YesNo',
                width: 300,
                xtype: 'commonSprCombo'
            }]
		});

        Ext6.apply(win, {
			items: [
				win.FormPanel
			],
			buttons: [{
				xtype: 'SimpleButton',
				handler:function () {
					win.hide();
				}
			},{
				xtype: 'SubmitButton',
				handler:function () {
					win.save();
				}
			}]
		});

		this.callParent(arguments);
		
        this.form = this.FormPanel.getForm();
    }
});