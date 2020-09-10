/*Карточка вызова 112*/
Ext.define('sw.tools.swCmpCallCard112', {
    alias: 'widget.swCmpCallCard112',
    extend: 'Ext.window.Window',
    title: 'Карточка вызова 112',
    width: 500,
    height: 600,
    layout: 'fit',
    modal: true,

    loadCmpCallCard112Data: function (baseForm, card_id) {

        Ext.Ajax.request({
            url: '/?c=CmpCallCard4E&m=loadCmpCallCard112EditForm',
            params: {CmpCallCard_id: card_id},
            callback: function (opt, success, response) {
                if (!success) {
                    return;
                }
                var response_obj = Ext.JSON.decode(response.responseText);
                if (!response_obj || !response_obj[0])
                    return;

                var me = this;
                baseForm.setValues(response_obj[0])
                //me.setValues(response_obj[0], baseForm);

            }.bind(this)
        })
    },

    setDisabledFields: function (form) {
        var allCmps = form.getFields();
        allCmps.filterBy(function (o, k) {
            o.setReadOnly(true)
            Ext.EventManager.purgeElement(o.getEl())
        })
    },
    initComponent: function () {

        var me = this;
        me.on('show', function () {

            me.baseForm = this.CmpCallCard112FormPanel.getForm();

            var config = arguments[0],
                view = config.view;

            switch (view) {
                case 'view' :
                {
                    me.setDisabledFields(me.baseForm)
                    me.loadCmpCallCard112Data(me.baseForm, config.card_id)
                    break
                }
            }
        });


        this.CmpCallCard112FormPanel = Ext.create('sw.BaseForm', {
            id: this.id + '_form',
            items: [
                {
                    xtype: 'container',
                    floatable: false,
                    region: 'middle',
                    splitterResize: false,
                    overflowY: 'scroll',
                    layout: {
                        align: 'right',
                        type: 'hbox'
                    },
                    items: [
                        {
                            xtype: 'container',
                            margin: '0 0 0 10',
                            defaultAlign: 'left',
                            layout: {
                                align: 'left',
                                type: 'vbox'
                            },
                            items: [
                                {
                                    xtype: 'fieldset',
                                    margin: '0 0 0 20',
                                    layout: {
                                        align: 'stretch',
                                        type: 'vbox'
                                    },
                                    width: 420,
                                    title: 'Общий раздел',
                                    items: [
                                        {
                                            xtype: 'numberfield',
                                            hideTrigger: true,
                                            keyNavEnabled: false,
                                            mouseWheelEnabled: false,
                                            labelWidth: 220,
                                            name: 'CmpCallCard112_id',
                                            fieldLabel: 'Идентификатор карточки вызова 112'
                                        }
                                    ]
                                },
                                {
                                    xtype: 'fieldset',
                                    margin: '0 0 0 20',
                                    layout: {
                                        align: 'stretch',
                                        type: 'vbox'
                                    },
                                    fieldDefaults: {
                                        labelWidth: 150
                                    },
                                    title: 'Информация о пациенте',
                                    width: 420,
                                    items: [
                                        {
                                            xtype: 'textfield',
                                            flex: 1,
                                            fieldLabel: 'Идентификатор',
                                            name: 'ExtPatientPerson_id'
                                        }, {
                                            xtype: 'textfield',
                                            fieldLabel: 'Повод',
                                            name: 'ExtPatientPerson_CallReasonStr'
                                        },
                                        {
                                            xtype: 'textfield',
                                            name: 'ExtPatientPerson_LastName',
                                            fieldLabel: 'Фамилия'
                                        }, {
                                            xtype: 'textfield',
                                            name: 'ExtPatientPerson_FirstName',
                                            fieldLabel: 'Имя'
                                        }, {
                                            xtype: 'textfield',
                                            name: 'ExtPatientPerson_MiddleName',
                                            fieldLabel: 'Отчество'
                                        }, {
                                            xtype: 'textfield',
                                            name: 'ExtPatientPerson_Gender',
                                            fieldLabel: 'Пол'
                                        }, {
                                            xtype: 'textfield',
                                            name: 'ExtPatientPerson_MoveAbility',
                                            fieldLabel: 'Способность к самостоятельному передвижению'
                                        }, {
                                            xtype: 'textfield',
                                            name: 'ExtPatientPerson_Age',
                                            fieldLabel: 'Возраст'
                                        }, {
                                            xtype: 'textfield',
                                            name: 'ExtPatientPerson_BirthdateIsoStr',
                                            fieldLabel: 'Дата рождения'
                                        }, {
                                            xtype: 'textfield',
                                            name: 'ExtPatientPerson_ExtId',
                                            fieldLabel: 'Идентификатор во внешней системе'
                                        }
                                    ]
                                },
                                {
                                    xtype: 'fieldset',
                                    margin: '0 0 0 20',
                                    layout: {
                                        align: 'stretch',
                                        type: 'vbox'
                                    },
                                    fieldDefaults: {
                                        labelWidth: 150
                                    },
                                    width: 420,
                                    title: 'Адрес места вызова',
                                    items: [
                                        {
                                            xtype: 'textfield',
                                            fieldLabel: 'Город',
                                            name: 'Address_City'
                                        }, {
                                            xtype: 'textfield',
                                            name: 'Address_CityShort',
                                            fieldLabel: 'Сокращение типа города или нас. пункта'
                                        }, {
                                            xtype: 'textfield',
                                            name: 'Address_District',
                                            fieldLabel: 'Район'
                                        }, {
                                            xtype: 'textfield',
                                            name: 'Address_Street',
                                            fieldLabel: 'Улица'
                                        }, {
                                            xtype: 'textfield',
                                            name: 'Address_StreetShort',
                                            fieldLabel: 'Сокращение типа улицы'
                                        }, {
                                            xtype: 'textfield',
                                            name: 'Address_HouseNumber',
                                            fieldLabel: 'Номер дома'
                                        }, {
                                            xtype: 'textfield',
                                            name: 'Address_HouseFraction',
                                            fieldLabel: 'Номер корпуса'
                                        }, {
                                            xtype: 'textfield',
                                            name: 'Address_Building',
                                            fieldLabel: 'Строение'
                                        }, {
                                            xtype: 'textfield',
                                            name: 'Address_Ownership',
                                            fieldLabel: 'Владение'
                                        }, {
                                            xtype: 'textfield',
                                            name: 'Address_TargetArea',
                                            fieldLabel: 'Адресный участок вне населенного пункта'
                                        }, {
                                            xtype: 'textfield',
                                            name: 'Address_TargetAreaStreet',
                                            fieldLabel: 'Улица вне населенного пункта'
                                        }, {
                                            xtype: 'textfield',
                                            name: 'Address_Road',
                                            fieldLabel: 'Дорога'
                                        }, {
                                            xtype: 'textfield',
                                            name: 'Address_Clarification',
                                            fieldLabel: 'Уточнение'
                                        }, {
                                            xtype: 'textfield',
                                            name: 'Address_Porch',
                                            fieldLabel: 'Подъезд'
                                        }, {
                                            xtype: 'textfield',
                                            name: 'Address_Floor',
                                            fieldLabel: 'Этаж'
                                        }, {
                                            xtype: 'textfield',
                                            name: 'Address_Flat',
                                            fieldLabel: 'Квартира'
                                        }, {
                                            xtype: 'textfield',
                                            name: 'Address_isNearText',
                                            fieldLabel: 'Место происшествия находится рядом'
                                        }, {
                                            xtype: 'textfield',
                                            name: 'Address_DistanceInKm',
                                            fieldLabel: 'Километр дороги'
                                        }, {
                                            xtype: 'textfield',
                                            name: 'Address_DistanceInM',
                                            fieldLabel: 'Уточнение места происшествия с точностью до 100 м'
                                        }, {
                                            xtype: 'textfield',
                                            name: 'Address_Code',
                                            fieldLabel: 'Код домофона'
                                        }
                                    ]
                                },
                                {
                                    xtype: 'fieldset',
                                    margin: '0 0 0 20',
                                    layout: {
                                        align: 'stretch',
                                        type: 'vbox'
                                    },
                                    fieldDefaults: {
                                        labelWidth: 150
                                    },
                                    width: 420,
                                    title: 'Координаты места вызова',
                                    items: [
                                        {
                                            xtype: 'textfield',
                                            name: 'Coords_Latitude',
                                            fieldLabel: 'Широта'
                                        }, {
                                            xtype: 'textfield',
                                            name: 'Coords_Longitude',
                                            fieldLabel: 'Долгота'
                                        }, {
                                            xtype: 'textfield',
                                            name: 'Coords_LapseRadius',
                                            fieldLabel: 'Погрешность'
                                        }
                                    ]
                                }
                                , {
                                    xtype: 'fieldset',
                                    margin: '0 0 0 20',
                                    layout: {
                                        align: 'stretch',
                                        type: 'vbox'
                                    },
                                    fieldDefaults: {
                                        labelWidth: 150
                                    },
                                    width: 420,
                                    title: 'Описание происшествия',
                                    items: [
                                        {
                                            xtype: 'textfield',
                                            name: 'CommonData_TypeStr',
                                            fieldLabel: 'Тип происшествия'
                                        }, {
                                            xtype: 'textfield',
                                            name: 'CommonData_RegionStr',
                                            fieldLabel: 'Регион / Район происшествия'
                                        }, {
                                            xtype: 'textfield',
                                            name: 'CommonData_HrId',
                                            fieldLabel: 'Краткий идентификатор происшествия'
                                        }, {
                                            xtype: 'textfield',
                                            name: 'CommonData_Description',
                                            fieldLabel: 'Описание'
                                        }, {
                                            xtype: 'textfield',
                                            name: 'CommonData_LostNumber',
                                            fieldLabel: 'Число погибших'
                                        }, {
                                            xtype: 'textfield',
                                            name: 'CommonData_InjuredNumber',
                                            fieldLabel: 'Число пострадавших'
                                        }, {
                                            xtype: 'textfield',
                                            name: 'CommonData_isDangerText',
                                            fieldLabel: 'Угроза людям'
                                        }, {
                                            xtype: 'textfield',
                                            name: 'CommonData_isBlockingText',
                                            fieldLabel: 'Блокирование'
                                        }, {
                                            xtype: 'textfield',
                                            name: 'CommonData_IsChemFloodText',
                                            fieldLabel: 'Разлив ядовитых веществ'
                                        }, {
                                            xtype: 'textfield',
                                            name: 'CommonData_IsMaliciousText',
                                            fieldLabel: 'Ложный вызов'
                                        }, {
                                            xtype: 'textfield',
                                            name: 'CommonData_TimeIsoStr',
                                            fieldLabel: 'Дата и время происшествия'
                                        }, {
                                            xtype: 'textfield',
                                            name: 'CommonData_Level',
                                            fieldLabel: 'Признак ЧС'
                                        }
                                    ]
                                },
                                {
                                    xtype: 'fieldset',
                                    margin: '0 0 0 20',
                                    layout: {
                                        align: 'stretch',
                                        type: 'vbox'
                                    },
                                    fieldDefaults: {
                                        labelWidth: 150
                                    },
                                    width: 420,
                                    title: 'Описание происшествия 03',
                                    items: [
                                        {
                                            xtype: 'textfield',
                                            name: 'DdsData03_DdsTypeStr',
                                            fieldLabel: 'Вид присшествия'
                                        }, {
                                            xtype: 'textfield',
                                            name: 'DdsData03_IsConsultationText',
                                            fieldLabel: 'Консультация'
                                        }, {
                                            xtype: 'textfield',
                                            name: 'DdsData03_CallerTypeStr',
                                            fieldLabel: 'Кто вызвал'
                                        }
                                    ]
                                },

                                {
                                    xtype: 'fieldset',
                                    margin: '0 0 0 20',
                                    layout: {
                                        align: 'stretch',
                                        type: 'vbox'
                                    },
                                    fieldDefaults: {
                                        labelWidth: 150
                                    },
                                    width: 420,
                                    title: 'Информация об обратившемся',
                                    items: [
                                        {
                                            xtype: 'textfield',
                                            name: 'Ier_City',
                                            fieldLabel: 'Город / Нас. пункт'
                                        }, {
                                            xtype: 'textfield',
                                            name: 'Ier_CityShort',
                                            fieldLabel: 'Сокращение типа города или нас. пункта'
                                        }, {
                                            xtype: 'textfield',
                                            name: 'Ier_District',
                                            fieldLabel: 'Район'
                                        }, {
                                            xtype: 'textfield',
                                            name: 'Ier_Street',
                                            fieldLabel: 'Улица'
                                        }, {
                                            xtype: 'textfield',
                                            name: 'Ier_StreetShort',
                                            fieldLabel: 'Сокращение типа улицы'
                                        }, {
                                            xtype: 'textfield',
                                            name: 'Ier_HouseNumber',
                                            fieldLabel: 'Номер дома'
                                        }, {
                                            xtype: 'textfield',
                                            name: 'Ier_HouseFraction',
                                            fieldLabel: 'Номер корпуса'
                                        }, {
                                            xtype: 'textfield',
                                            name: 'Ier_Building',
                                            fieldLabel: 'Строение'
                                        }, {
                                            xtype: 'textfield',
                                            name: 'Ier_Ownership',
                                            fieldLabel: 'Владение'
                                        }, {
                                            xtype: 'textfield',
                                            name: 'Ier_TargetArea',
                                            fieldLabel: 'Адресный участок вне населенного пункта'
                                        }, {
                                            xtype: 'textfield',
                                            name: 'Ier_TargetAreaStreet',
                                            fieldLabel: 'Улица вне населенного пункта'
                                        }, {
                                            xtype: 'textfield',
                                            name: 'Ier_Road',
                                            fieldLabel: 'Дорога'
                                        }, {
                                            xtype: 'textfield',
                                            name: 'Ier_Clarification',
                                            fieldLabel: 'Уточнение'
                                        }, {
                                            xtype: 'textfield',
                                            name: 'Ier_Porch',
                                            fieldLabel: 'Подъезд'
                                        }, {
                                            xtype: 'textfield',
                                            name: 'Ier_Floor',
                                            fieldLabel: 'Этаж'
                                        }, {
                                            xtype: 'textfield',
                                            name: 'Ier_Flat',
                                            fieldLabel: 'Квартира'
                                        }, {
                                            xtype: 'textfield',
                                            name: 'Ier_IsNearText',
                                            fieldLabel: 'Место происшествия находится рядом'
                                        }, {
                                            xtype: 'textfield',
                                            name: 'Ier_DistanceInKm',
                                            fieldLabel: 'Километр дороги'
                                        }, {
                                            xtype: 'textfield',
                                            name: 'Ier_DistanceInM',
                                            fieldLabel: 'Уточнение места происшествия с точностью до 100 м'
                                        }, {
                                            xtype: 'textfield',
                                            name: 'Ier_Code',
                                            fieldLabel: 'Код домофона'
                                        }, {
                                            xtype: 'textfield',
                                            name: 'Ier_Latitude',
                                            fieldLabel: 'Широта'
                                        }, {
                                            xtype: 'textfield',
                                            name: 'Ier_Longitude',
                                            fieldLabel: 'Долгота'
                                        }, {
                                            xtype: 'textfield',
                                            name: 'Ier_LapseRadius',
                                            fieldLabel: 'Погрешность'
                                        }, {
                                            xtype: 'textfield',
                                            name: 'Ier_id',
                                            fieldLabel: 'Идентификатор обращения'
                                        }, {
                                            xtype: 'textfield',
                                            name: 'Ier_CardId',
                                            fieldLabel: 'Идентификатор карточки вызова 112'
                                        }, {
                                            xtype: 'textfield',
                                            name: 'Ier_IerIsoTime',
                                            fieldLabel: 'Дата и время приема обращения'
                                        }, {
                                            xtype: 'textfield',
                                            name: 'Ier_CgPn',
                                            fieldLabel: 'Номер телефона, с которого поступило обращение'
                                        }, {
                                            xtype: 'textfield',
                                            name: 'Ier_CdPn',
                                            fieldLabel: 'Номер телефона диспетчера, принявшего обращение'
                                        }, {
                                            xtype: 'textfield',
                                            name: 'Ier_LastName',
                                            fieldLabel: 'Фамилия заявителя'
                                        }, {
                                            xtype: 'textfield',
                                            name: 'Ier_FirstName',
                                            fieldLabel: 'Имя заявителя'
                                        }, {
                                            xtype: 'textfield',
                                            name: 'Ier_MiddleName',
                                            fieldLabel: 'Отчество заявителя'
                                        }, {
                                            xtype: 'textfield',
                                            name: 'Ier_AcceptOperatorStr',
                                            fieldLabel: 'Логин диспетчера, принявшего обращение'
                                        }, {
                                            xtype: 'textfield',
                                            name: 'Ier_AcceptOperatorFio',
                                            fieldLabel: 'ФИО диспетчера, принявшего обращение'
                                        }, {
                                            xtype: 'textfield',
                                            name: 'Ier_Text',
                                            fieldLabel: 'Дополнительная информация о заявителе'
                                        }
                                    ]
                                }
                                , {
                                    xtype: 'fieldset',
                                    margin: '0 0 0 20',
                                    layout: {
                                        align: 'stretch',
                                        type: 'vbox'
                                    },
                                    fieldDefaults: {
                                        labelWidth: 150
                                    },
                                    width: 420,
                                    title: 'SMS-сообщение',
                                    items: [
                                        {
                                            xtype: 'textarea',
                                            name: 'Smsler_Text',
                                            fieldLabel: 'Текст SMS-сообщения'
                                        }
                                    ]
                                }


                            ]
                        }
                    ]
                }
            ]
        });


        Ext.applyIf(me, {
            items: [
                me.CmpCallCard112FormPanel
            ],
            dockedItems: [{
                xtype: 'toolbar',
                dock: 'bottom',
                items: [
                    '->',
                    {
                        xtype: 'button',
                        refId: 'cancelBtn',
                        iconCls: 'cancel16',
                        text: 'Закрыть',
                        margin: '0 5',
                        handler: function () {
                            this.up('window').close()
                        }
                    }]
            }]
        });

        me.callParent(arguments);

    }
})