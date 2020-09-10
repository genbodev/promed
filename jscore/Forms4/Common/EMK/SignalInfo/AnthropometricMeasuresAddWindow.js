/**
 * Форма льгот
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package
 * @access       public
 * @copyright    Copyright (c) 2018 Swan Ltd.
 *
 */
Ext6.define('common.EMK.SignalInfo.AnthropometricMeasuresAddWindow', {
    addCodeRefresh: Ext6.emptyFn,
    closeToolText: 'Закрыть',

    alias: 'widget.swAnthropometricMeasuresAddWindow',
    title: 'Антропометрические данные',
    extend: 'base.BaseForm',
    maximized: false,
    width: 455,
    height: 320,
    modal: true,

    findWindow: false,
    closable: true,
    cls: 'arm-window-new emk-forms-window privilege-window person-disp-diag-edit-window',
    renderTo: Ext6.getBody(), // main_center_panel.body.dom,
    layout: 'border',

    plain: true,
    resizable: false,

    doSave: function() {
        var current_window = this;
        var form = current_window.MainPanel;

        if ( !form.getForm().isValid() )
        {
            Ext6.Msg.show({
                buttons: Ext6.Msg.OK,
                fn: function() {
                    form.getFirstInvalidEl().focus(true);
                },
                icon: Ext6.Msg.WARNING,
                msg: ERR_INVFIELDS_MSG,
                title: ERR_INVFIELDS_TIT
            });
            return false;
        }

        var params = {};

	    form.getForm().submit({
            failure: function(form_temp, action) {
            	if(current_window.ownerPanel.ChartPanel.chart 
		            && current_window.MainPanel.getForm().findField('Person_id') == current_window.ownerPanel.ChartPanel.chart.Person_id) {
		            current_window.ownerPanel.ChartPanel.chart.load();
	            } else {
		            current_window.ownerPanel.load();
	            }
                current_window.hide();
            },
            params: params,
            success: function(form_temp, action) {
	            current_window.hide();
            }
        });
    },
    show: function(data) {
        this.callParent(arguments);
        var win = this;
        var form = this.MainPanel;
        var base_form = form.getForm();
        win.taskButton.hide();
        win.action = null;
        win.callback = Ext6.emptyFn;
        win.ARMType = '';

        win.ownerPanel = data.ownerPanel;
        
        form.getForm().reset();

        base_form.findField('Person_id').setValue(data.Person_id);
        base_form.findField('Server_id').setValue(data.Server_id);

        if (arguments[0].action)
        {
            win.action = arguments[0].action;
        }

        if (arguments[0].callback)
        {
            win.callback = arguments[0].callback;
        }


        if (arguments[0].ARMType)
        {
            win.ARMType = arguments[0].ARMType;
        }
        
        if(data.preload === true)
        	this.hide();

        switch (win.action)
        {
            case 'add':
                win.setTitle('Антропометрические данные');
                win.enableEdit(true);
                win.saveButton.setText(langs('Добавить'));
                win.saveButton.show();

                break;

            case 'edit':
            case 'view':
	            win.setTitle('Антропометрические данные редактирование');
	            win.enableEdit(true);
	            base_form.findField('PersonHeight_id').setValue(data.PersonHeight_id);
	            base_form.findField('PersonWeight_id').setValue(data.PersonWeight_id);
	            base_form.findField('PersonHeight_Height').setValue(data.PersonHeight_Height);
	            base_form.findField('PersonWeight_Weight').setValue(data.PersonWeight_Weight);
	            base_form.findField('MeasureType_id').setValue(data.MeasureType_id);
	            base_form.findField('PersonMeasure_setDate').setValue(data.Measure_setDate);
	            base_form.findField('Person_id').setValue(data.Person_id);
	            base_form.findField('Server_id').setValue(data.Server_id);

	            win.saveButton.setText('Изменить');
	            win.saveButton.show();

                break;
        }
    },
	//type:тип отклонения 'Рост' = 0 или 'Вес' = 1
	checkDeviation: function(this_field, new_value, type) {
    	var win = this;
		var base_form = win.MainPanel.getForm();
		var url,params;
		if (!type) {
			params = {
				Person_id: base_form.findField('Person_id').getValue(),
				PersonHeight_Height: parseFloat(new_value)
			};
			url = C_PERSON_DEVIATION_HEIGHT;
		} else {
			params = {
				Person_id: base_form.findField('Person_id').getValue(),
				PersonWeight_Weight: parseFloat(new_value)
			};
			url = C_PERSON_DEVIATION_WEIGHT;
		}

		Ext.Ajax.request(
			{
				url: url,
				params: params,
				callback: function (options, success, response) {
					if (success) {
						var data = false;
						if (response.responseText) {
							data = Ext.util.JSON.decode(response.responseText);
						}

						if (data) {
							//data[0] Id Отклонения
							//data[1] Мнимальное значение
							//data[2] Максимальное значение
							//data[3] Отклонение
							var max = parseFloat(data[2]),
								min = parseFloat(data[1]);
							var result = parseFloat(new_value);
								if (!type) {
									if (!win.MainPanel.HeightToolTip.target) {
										win.MainPanel.HeightToolTip.setTarget(this_field);
									}
									this_field.setFieldStyle('color:red');
									let msg = data[3] + ' Среднее значение: от ' + min.toString() + ' до ' +
										max.toString();
									win.MainPanel.HeightToolTip.setHtml(msg);
									win.MainPanel.HeightToolTip.enable();
									base_form.findField('PersonHeight_IsAbnorm').setValue(2);
								} else {
									if (!win.MainPanel.WeightToolTip.target) {
										win.MainPanel.WeightToolTip.setTarget(this_field);
									}
									this_field.setFieldStyle('color:red');
									let msg = data[3] + ' Среднее значение: от ' + min.toString() + ' до ' +
										max.toString();
									win.MainPanel.WeightToolTip.setHtml(msg);
									win.MainPanel.WeightToolTip.enable();
									base_form.findField('PersonWeight_IsAbnorm').setValue(2);
								}
							} else {
								if (!type) {
									this_field.setFieldStyle('color:black');
									win.MainPanel.HeightToolTip.disable();
									base_form.findField('PersonHeight_IsAbnorm').setValue(1);
								} else {
									this_field.setFieldStyle('color:black');
									win.MainPanel.WeightToolTip.disable();
									base_form.findField('PersonWeight_IsAbnorm').setValue(1);
								}
							}
					} else {
						Ext.Msg.alert(lang['oshibka'], lang['oshibka_zaprosa_k_serveru_poprobuyte_povtorit_operatsiyu']);
					}
				}
			});
		if (!type) {
			var weight = base_form.findField('PersonWeight_Weight').getValue();
			if (!weight) return;

			var ppt = Math.sqrt(new_value * weight / 3600).toFixed(2),
				imt = (weight / Math.pow(0.01 * new_value, 2)).toFixed(2);

		} else {
			var height = base_form.findField('PersonHeight_Height').getValue();
			if (!height) return;
			var ppt = Math.sqrt(new_value * height / 3600).toFixed(2),
				imt = (new_value / Math.pow(0.01 * height, 2)).toFixed(2);
		}
		base_form.findField('Person_PPT').setValue(ppt);
		base_form.findField('PersonWeight_Imt').setValue(imt);
	},
    initComponent: function() {
        var win = this;

        win.MainPanel = new Ext6.form.FormPanel({
            cls: 'emk_forms',
            bodyStyle: 'padding: 23px 32px 0px 32px',
            border: false,
            frame: false,
            labelAlign: 'right',
            items: [
                {
	                xtype: 'numberfield',
	                allowNegative: false,
	                maxValue: 300,
                    width: 150,
                    labelWidth: 82,
                    editable: win.action != 'view',
                    allowBlank: false,
                    regex: /([0-9]{1,3})$/,
                    anchor: '100%',
	                hideTrigger: true,
	                fieldLabel: langs('Рост, см'),
                    name: 'PersonHeight_Height',
                    listeners: {
                        'change': function(this_field, new_value) {
                            if (new_value) {
								win.checkDeviation(this_field,new_value,0);
                            }
                        }
                    }
                }, {
		            xtype: 'numberfield',
		            allowNegative: false,
                    width: 150,
		            maxValue: 500,
		            minValue: 0.7,
                    labelWidth: 82,
                    editable: win.action != 'view',
                    allowBlank: false,
		            hideTrigger: true,
                    regex: /([0-9]{1,3})$/,
                    anchor: '100%',
                    fieldLabel: langs('Вес, кг'),
                    name: 'PersonWeight_Weight',
                    listeners: {
                        'change': function(this_field, new_value) {
							if (new_value) {
								win.checkDeviation(this_field, new_value, 1);
							}
						}
                    }
                },{
                    width: 210,
                    labelWidth: 82,
                    editable: win.action != 'view',
                    allowBlank: false,
                    fieldLabel: langs('Дата'),
                    format: 'd.m.Y',
                    name: 'PersonMeasure_setDate',
                    plugins: [ new Ext6.ux.InputTextMask('99.99.9999', false) ],
                    value: new Date(),
		            maxValue: new Date(),
                    validateOnBlur: true,
		            xtype: 'datefield'
                },
                {
                    width: 335,
                    labelWidth: 82,
                    editable: win.action != 'view',
                    allowBlank: false,
                    fieldLabel: langs('Вид замера'),
                    xtype: 'commonSprCombo',
                    name: 'MeasureType_id',
                    hiddenName: 'HeightMeasureType_id',
                    displayField: 'HeightMeasureType_Name',
                    comboSubject: 'HeightMeasureType',
                    editable: false,
                    displayCode: false,
	                listeners: {
                    	select: function(combo, record, eOpts) {
                    		win.MainPanel.getForm().findField('MeasureType_id').setValue(record.get('HeightMeasureType_id'));
	                    }
	                }
                },
                {
                    width: 150,
                    labelWidth: 82,
                    fieldLabel: langs('ИМТ'),
                    xtype: 'displayfield',
                    name: 'PersonWeight_Imt',
	                fieldStyle: 'color:black; margin-top: 4px',
                    renderer: function (value, meta) {
                        if(!value) return '';
                        let max = 24.9,
                            min = 18.5;
                        var str = value;
                        var result = parseFloat(str.replace(/\s/g, ""));
                        var base_form = win.MainPanel.getForm();
                        if (!win.MainPanel.IMTToolTip.target)
                            win.MainPanel.IMTToolTip.setTarget(this);
                        if (result > max || result < min) {
                            this.setFieldStyle('color:red');
                            let msg = result > max ?'Превышение. Норма 24.9':'Нехватка. Норма 18.5';
                            win.MainPanel.IMTToolTip.setHtml(msg);
                            win.MainPanel.IMTToolTip.enable();
                            base_form.findField('PersonHeight_IsAbnorm').setValue(2);
                            base_form.findField('PersonWeight_IsAbnorm').setValue(2);
                        } else {
	                        this.setFieldStyle('color:black');
                            win.MainPanel.IMTToolTip.disable();
                            base_form.findField('PersonHeight_IsAbnorm').setValue(1);
                            base_form.findField('PersonWeight_IsAbnorm').setValue(1);
                        }
                        return value;
                    }
                }, {
                    width: 165,
                    labelWidth: 82,
                    readOnly: true,
                    fieldLabel: 'ППТ, м<sup>2</sup>',
                    xtype: "displayfield",
		            fieldStyle: 'color:black',
		            name: 'Person_PPT'
                },
                //скрытые поля
                { xtype: 'hidden', name: 'PersonHeight_id' },
                { xtype: 'hidden', name: 'PersonWeight_id' },
                { xtype: 'hidden', name: 'Okei_id', value: 37},//кг
                { xtype: 'hidden', name: 'Person_id' },
                { xtype: 'hidden', name: 'Server_id' },
                { xtype: 'hidden', name: 'PersonHeight_IsAbnorm' },
                { xtype: 'hidden', name: 'PersonWeight_IsAbnorm' },
                { xtype: 'hidden', name: 'HeightAbnormType_id' },
                { xtype: 'hidden', name: 'WeightAbnormType_id' }
            ],
            url: '/?c=Person&m=saveAnthropometryData',
            reader: Ext6.create('Ext6.data.reader.Json', {
                type: 'json',
                model: Ext6.create('Ext6.data.Model', {
                    fields:[
                        { name: 'Person_id'},
                        { name: 'Server_id'},
                        { name: 'PersonHeight_id' },
                        { name: 'PersonWeight_id' },
                        { name: 'PersonHeight_Height' },
                        { name: 'PersonWeight_Weight'},
                        { name: 'PersonHeight_IsAbnorm' },
                        { name: 'PersonWeight_IsAbnorm' },
                        { name: 'HeightAbnormType_id' },
                        { name: 'WeightAbnormType_id' },
                        { name: 'MeasureType_id'},
                        { name: 'PersonMeasure_setDate'},
                        { name: 'PersonWeight_Imt'},
                    ]
                })
            })
        });

        win.MainPanel.IMTToolTip = Ext6.create('Ext6.tip.ToolTip', {});
        win.MainPanel.HeightToolTip = Ext6.create('Ext6.tip.ToolTip', {});
        win.MainPanel.WeightToolTip = Ext6.create('Ext6.tip.ToolTip', {});

        win.saveButton = Ext6.create('Ext6.button.Button', {
            text: langs('Добавить'),
            itemId: 'button_save',
            margin: '0 19 0 0',
            handler: function() {
                win.doSave();
            }
        });

        Ext6.apply(win, {
            items: [
                win.MainPanel
            ],
            border: false,
            buttons:
                [ '->'
                    , {
                    text: langs('Отмена'),
                    userCls:'buttonPoupup buttonCancel',
                    handler: function() {
                        win.hide();
                    }
                }, win.saveButton]
        });
        this.callParent(arguments);
    }
});