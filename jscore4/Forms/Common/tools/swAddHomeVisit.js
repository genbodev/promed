/*История вызова*/
Ext.define('sw.tools.swAddHomeVisit', {
    alias: 'widget.swAddHomeVisit',
    extend: 'Ext.window.Window',
    title: 'Передача вызова в поликлинику',
    width: 500,
    height: 200,
    layout: 'fit',
    modal: true,

    initComponent: function () {

        var me = this;

		me.addEvents({
			addHomeVisit: true
		});

        me.on('show', function () {
            var config = arguments[0],
				setDT = config.params.CmpCallCard_prmDate ? new Date(config.params.CmpCallCard_prmDate) : new Date();

            me.baseform.getForm().setValues(config.params);

            me.baseform.getForm().findField('HomeVisit_setDate').setValue(Ext.Date.format(setDT, 'd.m.Y'));
            me.baseform.getForm().findField('HomeVisit_setTime').setValue(Ext.Date.format(setDT, 'H:i'));
        });
        me.baseform = Ext.create('sw.BaseForm', {
            xtype: 'BaseForm',
            items: [
                {
                    xtype: 'container',
                    width: '100%',
                    padding: '10',
                    layout: 'vbox',
                    defaults: {
                        labelAlign: 'left',
                        labelWidth: 120,
                        padding: '5 0 0 0'
                    },
                    items: [
                        {
                            xtype: 'lpuAllLocalCombo',
                            name: 'ComboValue_693',
                            fieldLabel: 'МО',
                            width: 400,
                            allowBlank: false,
                            //labelWidth: leftColumnLabelWidth,
                            autoFilter: true

                        },
                        {
                            xtype: 'container',
                            layout: 'hbox',
                            padding: '0',
                            items:[
                                {
                                    xtype: 'datefield',
                                    fieldLabel: 'Дата',
                                    labelWidth: 120,
                                    width: 250,
                                    format: 'd.m.Y',
                                    plugins: [new Ux.InputTextMask('99.99.9999')],
                                    validateOnBlur: false,
                                    validateOnChange: false,
                                    name: 'HomeVisit_setDate',
                                    allowBlank: false
                                },
                                {
                                    xtype: 'timefield',
                                    name: 'HomeVisit_setTime',
                                    format: 'H:i',
                                    invalidText: 'Неправильный формат времени. Дата должна быть указана в формате ЧЧ:ММ',
                                    plugins: [new Ux.InputTextMask('99:99')],
                                    validateOnBlur: false,
                                    validateOnChange: false,
                                    fieldLabel: 'Время',
                                    labelWidth: 40,
                                    width: 150,
                                    labelAlign: 'right',
                                    allowBlank: false,
                                    alias: 'widget.timeGetCurrentTimeCombo',
                                    triggerCls: 'x-form-clock-trigger',
                                    cls: 'stateCombo',
                                    onTriggerClick: function(e) {
                                        e.stopEvent();
                                        this.setValue(Ext.Date.format(new Date(), 'H:i'));
                                    }
                                }

                            ]
                        },
                        {
                            xtype: 'trigger',
                            fieldLabel: 'Адрес посещения',
                            width: 400,
                            name: 'ComboValue_711',
                            allowBlank: false,
                            trigger1Cls: 'x-form-search-trigger',
                            trigger2Cls: 'x-form-equil-trigger',
                            trigger3Cls: 'x-form-clear-trigger',
                            configObj: {
                                'Address_Zip':'Address_Zip',
                                'Country_id':'KLCountry_id',
                                'KLRegion_id':'ComboValue_703',
                                'KLSubRGN_id':'KLSubRGN_id',
                                'KLCity_id':'ComboValue_705',
                                'KLTown_id':'KLTown_id',
                                'KLStreet_id':'ComboValue_707',
                                'Corpus':'Address_Corpus',
                                'House':'ComboValue_708',
                                'Flat':'ComboValue_710',
                                'Address_begDate':'Address_begDate',
                                'full_address': 'ComboValue_711'
                            },
                            onTrigger1Click : function()
                            {
                                var field = this,
                                    addressObj = {};

                                for (var k in field.configObj) {
                                    if (field.configObj.hasOwnProperty(k) && me.baseform.getForm().findField(field.configObj[k])) {
                                        var val = me.baseform.getForm().findField(field.configObj[k]).getValue();
                                        addressObj[k] = isNaN(parseInt(val)) ? val : parseInt(val);
                                    }
                                }

                                Ext.create('common.tools.swAddressEditWindow',{
                                    fields: addressObj,
                                    callback: function(data){
                                        var key,
                                            setFieldValue = function(name,value) {
                                                var field = me.baseform.getForm().findField(name);
                                                if (field) {
                                                    field.setValue(value);
                                                }

                                            };
                                        for (key in field.configObj) {
                                            if (field.configObj.hasOwnProperty(key) && data.hasOwnProperty(key) && data[key]) {
                                                setFieldValue(field.configObj[key],data[key]);
                                            }
                                        }

                                    }
                                });
                            },
                            onTrigger2Click : function()
                            {
                                var key,
                                    copyFrom,
                                    copyTo,
                                    field = this,
                                    copyFromFieldConfig = me.baseform.getForm().findField('Address_Address').configObj || {},
                                    setFieldValue = function(name,value) {

                                        var field = me.baseform.getForm().findField(name);

                                        if (field) {
                                            field.setValue(value);
                                        }
                                    };
                                var getFieldValue = function(name) {

                                    var field = me.baseform.getForm().findField(name);

                                    if (field) {
                                        return field.getValue();
                                    }
                                };

                                for (key in field.configObj) {
                                    if (field.configObj.hasOwnProperty(key) && copyFromFieldConfig.hasOwnProperty(key)) {

                                        copyFrom = copyFromFieldConfig[key];
                                        copyTo = field.configObj[key];

                                        setFieldValue(copyTo, getFieldValue(copyFrom));

                                    }
                                }
                            },
                            onTrigger3Click: function() {
                                var field = this,
                                    key,
                                    setFieldValue = function(name,value) {
                                        var field = me.baseform.getForm().findField(name);
                                        if (field) {
                                            field.setValue(value);
                                        }
                                    };

                                for (key in field.configObj) {
                                    if (field.configObj.hasOwnProperty(key) && field.configObj[key]) {
                                        setFieldValue(field.configObj[key],null);
                                    }
                                }
                            }
                        },
                        {
                            xtype: 'hidden',
                            name: 'Person_id'
                        },
                        {
                            xtype: 'hidden',
                            name: 'CmpCallCard_id'
                        },
                        {
                            xtype: 'hidden',
                            name: 'Address_Zip'
                        },
                        {
                            xtype: 'hidden',
                            name: 'KLCountry_id'
                        },
                        {
                            xtype: 'hidden',
                            name: 'ComboValue_703'
                        },
                        {
                            xtype: 'hidden',
                            name: 'KLSubRGN_id'
                        },
                        {
                            xtype: 'hidden',
                            name: 'ComboValue_705'
                        },
                        {
                            xtype: 'hidden',
                            name: 'KLTown_id'
                        },
                        {
                            xtype: 'hidden',
                            name: 'ComboValue_707'
                        },
                        {
                            xtype: 'hidden',
                            name: 'Address_Corpus'
                        },
                        {
                            xtype: 'hidden',
                            name: 'ComboValue_708'
                        },
                        {
                            xtype: 'hidden',
                            name: 'ComboValue_710'
                        }
                    ]
                }]
        });

        Ext.applyIf(me, {

            items: [
                me.baseform
            ],
            dockedItems: [{
                xtype: 'toolbar',
                dock: 'bottom',
                items: [
                    {
                        xtype: 'button',
                        refId: 'saveBtn',
                        iconCls: 'save16',
                        text: 'Сохранить',
                        margin: '0 5',
                        handler: function () {
                            me.saveHomeVisit();
                        }
                    },
                    '->',
                    {
                        text: 'Помощь',
                        iconCls   : 'help16',
                        handler   : function()
                        {
                            ShowHelp(me.title);
                        }
                    },
                    {
                        xtype: 'button',
                        refId: 'cancelBtn',
                        iconCls: 'cancel16',
                        text: 'Отмена',
                        margin: '0 5',
                        handler: function () {
                            this.up('window').close()
                        }
                    }]
            }]
        });

        me.callParent(arguments);

    },
    saveHomeVisit: function(){
        var me = this,
            base_form = this.baseform.getForm(),
            params = base_form.getValues();

        params.ComboValue_694 = params.HomeVisit_setDate + ' ' + params.HomeVisit_setTime;

        Ext.Ajax.request({
            url: '/?c=CmpCallCard&m=addHomeVisitFromSMP',
            params: params,
            callback: function (opt, success, response) {

				var res = Ext.JSON.decode(response.responseText);

                if(res && res[0]){

					if(res[0].success){
						Ext.Ajax.request({
							url: '/?c=CmpCallCard4E&m=setStatusCmpCallCard',
							params: {
								CmpCallCardStatusType_id: 4, //Обслужено
								CmpCallCard_id:	params['CmpCallCard_id'],
								armtype: 'smpdispatchstation'
							},
							callback: function (opt, success, response) {
								me.fireEvent('addHomeVisit');
								me.close();
							}
						});

						Ext.Ajax.request({
							url: '/?c=CmpCallCard&m=setResult',
							params: {
								CmpPPDResult_Code: 23, //Вызов передан уч. врачу
								CmpCallCard_id: params['CmpCallCard_id']
							}
						});
					}else{

						var nearestDateTime = res[0].Error_Msg,
							nearestDateTimeDD = Ext.Date.parse(nearestDateTime, 'd.m.Y H:i');

                       if(nearestDateTimeDD){
						   Ext.MessageBox.show({
							   title: 'Внимание',
							   msg: 'Указанные дата и время недоступны для выбора.</br>Выбрать ближайшие доступные дату и время ' + nearestDateTime + ' ?',
							   buttons: Ext.Msg.YESNO,
							   buttonText :
							   {
								   yes : 'Да',
								   no : 'Нет'
							   },
							   fn: function(btn){
								   if (btn == 'yes'){
									   me.baseform.getForm().findField('HomeVisit_setDate').setValue(Ext.Date.format(nearestDateTimeDD, 'd.m.Y'));
									   me.baseform.getForm().findField('HomeVisit_setTime').setValue(Ext.Date.format(nearestDateTimeDD, 'H:i'));
									   me.saveHomeVisit();
								   }
								   else
									   return false;
							   }
						   });
					   }else{
						   Ext.MessageBox.alert('Ошибка', nearestDateTime)
					   }


					}


				};

            }
        });

    }
})