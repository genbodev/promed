/**
* swCmpCallCardAirEditWindow - окно редактирования карты вызова (краткий вариант для операторов СМП)
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Ambulance
* @access       public
* @copyright    Copyright (c) 2010 Swan Ltd.
* @author		Bykov Stas aka Savage (savage@swan.perm.ru)
* @version      апрель.2012
*/

sw.Promed.swCmpCallCardAirEditWindow = Ext.extend(sw.Promed.BaseForm, {
	objectName: 'swCmpCallCardAirEditWindow',
	objectSrc: '/jscore/Forms/Ambulance/swCmpCallCardAirEditWindow.js',
	closable: true,
	closeAction: 'hide',
	maximizable: true,
	maximized: true,
	plain: false,
	width: 750,
	layout: 'form',

	initComponent: function() {
		var me = this,
			opts = getGlobalOptions();

		//console.warn(arguments)

        me.initUslugaViewframe(me);

		//нумерация филдсетов
		me.panelNumber = 0;

		me.FormPanel = new Ext.form.FormPanel({
			autoScroll: true,
			toolbar: true,
			bodyBorder: false,
			bodyStyle: 'padding: 5px',
			border: false,
			frame: true,
			labelAlign: 'right',
			labelWidth: 350,
			region: 'center',
			items: [
                {
                    xtype: 'hidden',
                    name: 'CmpCallCard_id',
                },
				{
					title: ++me.panelNumber + '. Информация о вызове',
					xtype      : 'fieldset',
					autoHeight: true,
					items : [
                        {
                            xtype: 'hidden',
                            name: 'CmpCallCard_prmDate'
                        }, {
                            xtype: 'hidden',
                            name: 'CmpCallCard_prmTime'
                        },
						{
							xtype: 'textfield',
							fieldLabel: lang['№_vyizova_za_den'],
							name: 'CmpCallCard_Numv',
							allowBlank: false,
							maskRe: /[0-9]/,
							maxLength: 12
						},
						{
							xtype: 'textfield',
							fieldLabel: lang['№_vyizova_za_god'],
							name: 'CmpCallCard_Ngod',
							allowBlank: false,
							maskRe: /[0-9]/,
							maxLength: 12
						},
						{
							comboSubject: 'CmpLeaveType',
							fieldLabel	   : lang['vid_viezda'],
							hiddenName: 'CmpLeaveType_id',
							name: 'CmpLeaveType_id',
							xtype: 'swcommonsprcombo',
                            allowBlank: false,
							width: 250,
							listWidth: 250
						},
						{
							comboSubject: 'CmpLeaveTask',
							fieldLabel	   : lang['zadanie'],
							hiddenName: 'CmpLeaveTask_id',
							name: 'CmpLeaveTask_id',
							xtype: 'swcommonsprcombo',
                            allowBlank: false,
							width: 250,
							listWidth: 250
						},
						{
							comboSubject: 'CmpMedicalCareKind',
							fieldLabel	   : lang['vid_medicinskoy_pomoshi'],
							hiddenName: 'CmpMedicalCareKind_id',
							name: 'CmpMedicalCareKind_id',
							xtype: 'swcommonsprcombo',
                            allowBlank: false,
							width: 250,
							listWidth: 250
						}
					]
				},
				{
					title: ++me.panelNumber + '. Время',
					xtype      : 'fieldset',
					id : me.id + '_timeBlock',
					autoHeight: true,
					items : [
						{
							dateLabel: 'Приема вызова',
							hiddenName: 'CmpCallCard_prmDT',
							xtype: 'swdatetimefield',
                            allowBlank: false,
                            onChange: function(field, newValue){
                                var base_form = me.FormPanel.getForm();

                                base_form.findField('EmergencyTeam_id').store.load({
                                    params: {
                                        AcceptTime: Ext.util.Format.date(new Date(field.getValue()), 'd.m.Y H:i:s'),
                                        CmpCallCard_id: base_form.findField('CmpCallCard_id')
                                    }
                                });
                            },
							listeners: {
								'blur': function(){
									var base_form = me.FormPanel.getForm();

									me.setPersonAgeFields('CmpCallCard_prmDate');
								}
							}
						},
						{
							dateLabel: 'Выезда в пункт назначения',
							hiddenName: 'CmpCallCard_Vyez',
							xtype: 'swdatetimefield',
                            allowBlank: false
						},
						{
							dateLabel: 'Прибытия в пункт назначения',
							hiddenName: 'CmpCallCard_Przd',
							xtype: 'swdatetimefield',
                            allowBlank: false
						},
						{
							dateLabel: 'Убытия из пункта назначения',
							hiddenName: 'CmpCallCard_Tgsp',
							xtype: 'swdatetimefield',
                            allowBlank: false
						},
						{
							dateLabel: 'Возвращения',
							hiddenName: 'CmpCallCard_Tvzv',
							xtype: 'swdatetimefield',
                            allowBlank: false
						}
					]
				},				
				{
					title: ++me.panelNumber + '. Санитарно-авиационная бригада',
					xtype      : 'fieldset',
					autoHeight: true,
					items : [
						{
							xtype: 'swsmpunitscombo',
							fieldLabel: 'Станция (подстанция), отделение',
							hiddenName:'LpuBuilding_id',
							disabledClass: 'field-disabled',
							width: 350,
							allowBlank: false,
							listWidth: 300
						},
						/*{
							xtype: 'swemergencyteamorepenvcombo',
							fieldLabel:	'Санитарно-авиационная бригада ',
							hiddenName: 'EmergencyTeam_id',
							allowBlank: false,
							width: 350,
							listWidth: 350
						},*/
                        {
                            xtype: 'swEmergencyTeamCCC',
                            fieldLabel:	'Санитарно-авиационная бригада',
                            hiddenName: 'EmergencyTeam_id',
                            allowBlank: false,
                            width: 350,
                            listWidth: 350,
                            listeners: {

                            }
                        },
						{
							comboSubject: 'CmpTransportType',
							fieldLabel: lang['vid_transporta'],
							hiddenName: 'CmpTransportType_id',
							name: 'CmpTransportType_id',
							xtype: 'swcommonsprcombo',
                            allowBlank: false,
							width: 250,
							listWidth: 250
						}
					]
				},
				{
					autoHeight: true,
					title: ++me.panelNumber + '. '+lang['svedeniya_o_bolnom'],
					xtype: 'fieldset',
                    name: 'personFieldset',
					items: [{
						border: false,
						layout: 'column',				
						items: [
                            {
                                border: false,
                                layout: 'form',
                                items: [
                                    {
                                        xtype: 'hidden',
                                        name: 'Person_Age'
                                    },
                                    {
										xtype: 'swdatefield',
                                        name: 'Person_BirthDay',
										hidden: true,
										labelStyle: 'display: none;'
                                    },
                                    {
                                        disabledClass: 'field-disabled',
                                        fieldLabel: lang['familiya'],
                                        name: 'Person_SurName',
                                        toUpperCase: true,
                                        allowBlank: false,
                                        width: 180,
                                        xtype: 'textfieldpmw'
                                    },
                                    {
                                        disabledClass: 'field-disabled',
                                        fieldLabel: lang['imya'],
                                        name: 'Person_FirName',
                                        toUpperCase: true,
                                        allowBlank: false,
                                        width: 180,
                                        xtype: 'textfieldpmw'
                                    },
                                    {
                                        disabledClass: 'field-disabled',
                                        fieldLabel: lang['otchestvo'],
                                        name: 'Person_SecName',
                                        toUpperCase: true,
                                        width: 180,
                                        xtype: 'textfieldpmw'
                                    },
                                    /*{
                                        name: 'Person_BirthDay',
                                        maxValue: (new Date()),
                                        plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
                                        fieldLabel: lang['data_rojdeniya'],
                                        xtype: 'swdatefield'
                                    },*/
                                    {
                                        layout: 'column',
                                        border: false,
                                        items: [
                                            {
                                                layout: 'form',
                                                border: false,
                                                items: [{
                                                    allowDecimals: false,
                                                    allowNegative: false,
                                                    allowBlank: false,
                                                    disabledClass: 'field-disabled',
                                                    fieldLabel: lang['vozrast'],
                                                    name: 'Person_Age_Inp',
                                                    // tabIndex: TABINDEX_PEF + 4,
                                                    width: 100,
                                                    xtype: 'numberfield',
													listeners: {
														blur: function() {
															me.setPersonAgeFields('Person_Age_Inp');
														}
													}
                                                }]
                                            },
                                            {
                                                layout: 'form',
                                                border: false,
                                                items: [
                                                    {
                                                        allowBlank: false,
                                                        xtype: 'swstoreinconfigcombo',
                                                        hideLabel: true,
                                                        hiddenName: 'AgeUnit_id',
                                                        valueField: 'AgeUnit_id',
                                                        displayField: 'AgeUnit_Name',
                                                        comboData: [
                                                            ['years', 'лет'],
                                                            ['months', 'месяцев'],
                                                            ['days', 'дней']
                                                        ],
                                                        comboFields: [
                                                            {name: 'AgeUnit_id', type:'string'},
                                                            {name: 'AgeUnit_Name', type:'string'}
                                                        ],
                                                        value: 'years',
                                                        width: 60,
                                                        listeners: 	{
                                                            'select': function(combo, record, index) {
																me.setPersonAgeFields('AgeUnit_id');
                                                            }
                                                        }
                                                    }
                                                ]
                                            }
                                        ]
                                    },
                                    /*{
                                        comboSubject: 'Sex',
                                        disabledClass: 'field-disabled',
                                        fieldLabel: lang['pol'],
                                        allowBlank: false,
                                        hiddenName: 'Sex_id',
                                        width: 130,
                                        xtype: 'swcommonsprcombo'
                                    }*/
                                ]
                            },
                            {
                                border: false,
                                layout: 'form',
								refId: 'personButton',
                                style: 'padding-left: 10px;',
                                items: [
                                    {
                                        handler: function() {
                                            me.personSearch(me);
                                        },
                                        iconCls: 'search16',
										refId: 'searchBtn',
                                        text: langs('Поиск'),
                                        xtype: 'button'
                                    },
                                    {
                                        handler: function() {
                                            me.personReset();
                                        },
                                        iconCls: 'reset16',
										refId: 'resetBtn',
                                        text: langs('Сброс'),
                                        xtype: 'button'
                                    },
                                    {
                                        handler: function() {
                                            me.personUnknown();
                                        },
                                        iconCls: 'reset16',
										refId: 'unknownBtn',
                                        text: langs('НЕИЗВЕСТЕН'),
                                        xtype: 'button'
                                    }
                                ]
                            }
                        ]
					}]
				},
				{
					autoHeight: true,
					title: ++me.panelNumber + '. '+lang['punkt_naznacheniya'],
					xtype: 'fieldset',
                    name: 'addressFieldset',
					items: [{
						border: false,
						layout: 'column',
						style: 'padding: 0px;',
						items: [{
							border: false,
							layout: 'form',
							style: 'padding: 0px',
							items: [
								{
									//fieldLabel: 'МО передачи (НМП)',
									valueField: 'Lpu_id',
									hiddenName   : 'Lpu_hid',
									fieldLabel: "Пункт назначения",
									autoLoad: true,
									width: 350,
									listWidth: 350,
									disabledClass: 'field-disabled',
									displayField: 'Lpu_Nick',
									//medServiceTypeId: 18,
									comAction: 'AllAddress',
									editable: true,
									xtype: 'swlpuopenedcombo',
								},
								/*{
									xtype: "sworgcomboex",
									fieldLabel: "Пункт назначения",									
									enableKeyEvents: true,
									triggerAction: "none",
									width: 320,
                                    hiddenName   : 'Org_id',
									enableOrgType: false,
									defaultOrgType: 11,
                                    allowBlank: false,
									autoLoad: true,
                                    listeners: {
                                        select: function(combo, rec){
                                            var base_form = me.FormPanel.getForm();

                                            base_form.findField('Lpu_hid').setValue(rec.get('Lpu_id'))
                                        }
                                    }
								},
                                {
                                    xtype: 'hidden',
                                    name: 'Lpu_hid'
                                },*/
								{
									enableKeyEvents: true,
									hiddenName: 'KLAreaStat_idEdit',
									listeners: {},
									width: 180,
									xtype: 'swklareastatcombo',
                                    listeners: {
                                        beforeselect: function(combo, record) {
                                            me.setAddress(combo, record);
                                        }
                                    }
								}, 
								{
									hiddenName: 'KLSubRgn_id',
									listeners: {},
									width: 180,
									xtype: 'swsubrgncombo',
                                    listeners: {
                                        beforeselect: function(combo, record) {
                                            me.setAddress(combo, record);
                                        }
                                    }
								}, 
								{
									hiddenName: 'KLCity_id',
									listeners: {},
									width: 180,
									trigger2Class: 'x-form-clear-trigger',
									xtype: 'swcitycombo',
                                    listeners: {
                                        beforeselect: function(combo, record) {
                                            me.setAddress(combo, record);
                                        }
                                    },
                                    onTrigger2Click: function() {
                                        var base_form = me.FormPanel.getForm();

                                        base_form.findField('KLCity_id').clearValue();
                                        base_form.findField('KLTown_id').clearValue();

                                    }.createDelegate(this)
								}, 
								{									
									hiddenName: 'KLTown_id',
									enableKeyEvents: true,
									listeners: {},
									width: 250,
									xtype: 'swtowncombo',
                                    listeners: {
                                        beforeselect: function(combo, record) {
                                            me.setAddress(combo, record);
                                        }
                                    },
                                    onTrigger2Click: function() {
                                        var base_form = me.FormPanel.getForm();

                                        getWnd('swKLTownSearchWindow').show({
                                            onSelect: function(response_data) {
                                                base_form.findField('KLAreaStat_idEdit').onClearValue();
                                                this.reloadAllFields(response_data);
                                            }.createDelegate(this),
                                            params: {
                                                KLCity_id: base_form.findField('KLCity_id').getValue() || '0',
                                                KLSubRegion_id: base_form.findField('KLSubRgn_id').getValue() || '0',
                                                KLCity_Name: base_form.findField('KLCity_id').getRawValue() || '',
                                                KLSubRegion_Name: base_form.findField('KLSubRgn_id').getRawValue() || '',
                                                KLRegion_id: opts.region.number,
                                                KLRegion_Name: (opts.region.number == 2) ? lang['bashkortostan'] : opts.region.name
                                            }
                                        });
                                    }.createDelegate(this)
								}, 
								{
									xtype: 'swstreetandunformalizedaddresscombo',
									fieldLabel: lang['ulitsa'],
									hiddenName: 'StreetAndUnformalizedAddressDirectory_id',
                                    listeners: {
                                        blur: function(combo){

                                            var base_form = me.FormPanel.getForm();

                                            if(
                                                !combo.store.getCount() ||
                                                combo.store.findBy(function(rec) { return rec.get('StreetAndUnformalizedAddressDirectory_id') == combo.getValue(); }) == -1
                                            )
                                            {
                                                if(getRegionNick().inlist(['krym'])){
                                                    base_form.findField('UnformalizedAddressDirectory_id').setValue(null);
                                                    base_form.findField('KLStreet_id').setValue(null);
                                                    base_form.findField('CmpCallCard_Ulic').setValue(c.getRawValue());
                                                }
                                                else{
                                                    combo.reset();
                                                }
                                            }

                                        },
                                        beforeselect: function(combo, record) {
                                            if ( typeof record != 'undefined' ) {
                                                combo.setValue(record.get(combo.valueField));
                                            }

                                            var base_form = me.FormPanel.getForm();
                                            base_form.findField('UnformalizedAddressDirectory_id').setValue(record.get('UnformalizedAddressDirectory_id'));
                                            base_form.findField('KLStreet_id').setValue(record.get('KLStreet_id'));
                                        }
                                    },
									width: 250,
									editable: true
								},
                                {
                                    xtype: 'hidden',
                                    name: 'KLStreet_id'
                                },
                                {
                                    xtype: 'hidden',
                                    name: 'UnformalizedAddressDirectory_id'
                                },
                                {
                                    xtype: 'hidden',
                                    name: 'CmpCallCard_Ulic'
                                },
								{
									disabledClass: 'field-disabled',
									fieldLabel: lang['dom'],
									name: 'CmpCallCard_Dom',
									width: 100,
									toUpperCase: true,
									xtype: 'textfield'
								}, 
								{
									disabledClass: 'field-disabled',
									fieldLabel: lang['korpus'],
									name: 'CmpCallCard_Korp',
									width: 100,
									toUpperCase: true,
									xtype: 'textfield'
								}								
							]
						}]
					}]
				},
				{
					autoHeight: true,					
					title: ++me.panelNumber + '. '+lang['otchet_konsultanta'],
					xtype: 'fieldset',
					items: [{
						border: false,
						layout: 'column',
						style: 'padding: 0px;',
						items: [{
							border: false,
							layout: 'form',
							style: 'padding: 0px',
							items: [
								{
									checkAccessRights: true,
									hiddenName: 'Diag_sid',
									xtype: 'swdiagcombo',
									allowBlank: false,
									//withGroups: getGlobalOptions().region.nick.inlist(['perm']),
									disabledClass: 'field-disabled',
									MKB: {
										isMain: true
									},
									width: 250,
									listeners: {}
								},
								{
									comboSubject: 'CmpResultDeseaseType',
									fieldLabel: lang['ishod'],
									hiddenName: 'CmpResultDeseaseType_id',
									name: 'CmpResultDeseaseType_id',
									xtype: 'swcommonsprcombo',
                                    allowBlank: false,
									width: 250,
									listWidth: 250
								},
								{
									comboSubject: 'CmpCallCardResult',
									fieldLabel: lang['rezultat'],
									hiddenName: 'CmpCallCardResult_id',
									name: 'CmpCallCardResult_id',
                                    allowBlank: false,
									xtype: 'swcommonsprcombo',
									width: 250,
									listWidth: 250
								},
								{
									disabledClass: 'field-disabled',
									fieldLabel: lang['primechanie'],
									name: 'CmpCallCard_Comm',
									width: 250,
									xtype: 'textfield'
								}
							]
						}]
					}]
				},
                {
                    autoHeight: true,
                    title: ++me.panelNumber + '. '+lang['uslugi'],
                    xtype: 'fieldset',
                    items: [
                        me.UslugaViewFrame
                    ]
                }
			]
		});

		Ext.apply(me, {
			buttons: [
				{
					handler: function() {
						me.doSave();
					},
					iconCls: 'save16',
					text: BTN_FRMSAVE
				},
				{
					text: '-'
				},
					HelpButton(me, -1),
				{
					handler: function() {
						me.hide();
					},
					iconCls: 'cancel16',
					text: BTN_FRMCANCEL
				}
			],
			items: [
				me.FormPanel
			],
			layout: 'border'
		});

		sw.Promed.swCmpCallCardAirEditWindow.superclass.initComponent.apply(me, arguments);
	},

	listeners: {},

	show: function(opts) {
		var me = this,
            base_form = me.FormPanel.getForm(),
			defaultTitle = 'Карта вызова по санавиации';

        base_form.reset();

        me.action = opts.action;

		switch(me.action){
			case 'add' : {
				me.setTitle(defaultTitle+ ': Добавление');
				me.setDisabledFields(base_form,false);
				me.buttons[0].enable();
				break;
			}
			case 'view' : {
				me.setTitle(defaultTitle+ ': Просмотр');
				me.setDisabledFields(base_form,true);
				me.buttons[0].disable();
				break;
			}
			case 'edit' : {
				me.setTitle(defaultTitle+ ': Редактирование');
				me.setDisabledFields(base_form,false);
				me.buttons[0].enable();
				break;
			}
		};

        me.loadData(me, base_form, opts);

        base_form.isValid();

		sw.Promed.swCmpCallCardAirEditWindow.superclass.show.apply(me, arguments);
	},
	setDisabledFields: function(base_form,disable){

		var me = this;
		var disableFields = [
			'CmpCallCard_prmDate',
			'CmpCallCard_prmTime',
			'CmpCallCard_Numv',
			'CmpCallCard_Ngod',
			'CmpLeaveType_id',
			'CmpLeaveTask_id',
			'CmpMedicalCareKind_id',
			'CmpTransportType_id',
			'Person_SurName',
			'Person_FirName',
			'Person_SecName',
			'Person_Age_Inp',
			'LpuBuilding_id',
			'EmergencyTeam_id',
			'AgeUnit_id',
			'Lpu_hid',
			'KLAreaStat_idEdit',
			'KLSubRgn_id',
			'KLCity_id',
			'KLTown_id',
			'StreetAndUnformalizedAddressDirectory_id',
			'CmpCallCard_Dom',
			'CmpCallCard_Korp',
			'Diag_sid',
			'CmpResultDeseaseType_id',
			'CmpCallCardResult_id',
			'CmpCallCard_Comm',
			'CmpCallCard_prmDT',
			'CmpCallCard_Vyez',
			'CmpCallCard_Przd',
			'CmpCallCard_Tgsp',
			'CmpCallCard_Tvzv',
			'searchBtn',
			'resetBtn',
			'unknownBtn',
			'id_action_add',
			'id_action_delete',
			'id_action_edit'
		];
		disableFields.forEach(function(item){
			switch (item){
				case 'CmpCallCard_prmDT':
				case 'CmpCallCard_Vyez':
				case 'CmpCallCard_Przd':
				case 'CmpCallCard_Tgsp':
				case 'CmpCallCard_Tvzv': {
					base_form.findField(item).ownerCt.setDisabled(disable);
					break;
				}
				case 'searchBtn':
				case 'resetBtn':
				case 'unknownBtn': {
					me.FormPanel.find('refId', item)[0].setDisabled(disable);

					break;
				}
				case 'id_action_add':
				case 'id_action_edit':
				case 'id_action_delete': {
					Ext.getCmp(item).setDisabled(disable);
					break;
				}
				default: {
					base_form.findField(item).setDisabled(disable);
				}
			}
		});

	},
    loadData: function(me, form, opts){
        var formParams = {};
        if(me.action != 'add'){
            Ext.Ajax.request({
                url: '/?c=CmpCallCard&m=loadCmpCallCardEditForm',
                params: {
                    CmpCallCard_id: opts.card_id
                },
                success: function (response){
                    formParams = Ext.util.JSON.decode(response.responseText);
                    me.setValues(me, form, formParams[0]);
                },
                failure: function (a,b,c) {
                    sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_zagruzke_dannyih_formyi'], function () {});
                }

            });
        }
        else {
            formParams = {
                CmpLeaveType_id: 1,
                CmpLeaveTask_id: 1,
                CmpMedicalCareKind_id: 1,
                CmpCallCard_prmDT: new Date()
            };
            me.setValues(me, form, formParams);
            me.getCmpCallCardNumber(me, form);
        };
    },

    //загрузка полей и установка значений, зависимостей
    setValues: function(me, form, formParams){
        var fields = me.getAllFields(),
            formParams = formParams || {},
			base_form = me.FormPanel.getForm(),
			opts = getGlobalOptions();

        for(var i = 0; i < fields.length; i++){
            var fieldCmp = fields[i],
                fieldName = fieldCmp.getName() || fieldCmp.hiddenName,
                fieldVal = fieldCmp.getValue();

            switch(fieldName){
                case 'EmergencyTeam_id' : {
					me.setValueAfterStoreLoad(
                        fieldCmp,
                        formParams.EmergencyTeam_id,
                        {
                            AcceptTime: Ext.util.Format.date(new Date(), 'd.m.Y H:i:s'),
                            CmpCallCard_id: null
                        }
                    );
                    break;
                }
                case 'CmpCallCard_prmDate': {
                    fieldCmp.setValue(new Date().format("d.m.Y"));
                    break;
                }
                case 'CmpCallCard_prmTime': {
                    fieldCmp.setValue(new Date().format("H:i"));
                    break;
                }
                case 'Person_Age_Inp': {
                    if(formParams.Person_Age) {
						fieldCmp.setValue(formParams.Person_Age);
					}
					//иногда приходит возраст или дата рождения, тогда нужно проставлять недостающее значение
					if(!formParams.Person_Age || !formParams.Person_BirthDay){
						if(formParams.Person_Age || formParams.Person_BirthDay){
							var issetField = formParams.Person_Age ? 'Person_Age' : 'Person_BirthDay';
							me.setPersonAgeFields(issetField);
						};
					};
                    break;
                }
                case 'Diag_sid':{
                    if(formParams.Diag_sid){
						me.setValueAfterStoreLoad(fieldCmp, formParams.Diag_sid, {where: "where Diag_id = " + formParams.Diag_sid}, null);
                    }
                    break;
                }
				case 'KLAreaStat_idEdit': {
					var rec = -1;

					switch(true){
						case ( !Ext.isEmpty(formParams.KLTown_id) ) :{
							rec = fieldCmp.getStore().find( 'KLTown_id', formParams.KLSubRgn_id, 0, false );
							break;
						}
						case ( !Ext.isEmpty(formParams.KLCity_id) ) :{
							rec = fieldCmp.getStore().find( 'KLCity_id', formParams.KLCity_id, 0, false );
							break;
						}
						case ( !Ext.isEmpty(formParams.KLSubRGN_id) ) :{
							rec = fieldCmp.getStore().find( 'KLSubRGN_id', formParams.KLTown_id, 0, false );
							break;
						}
					};

					if(rec != -1) {
						fieldCmp.setValue( fieldCmp.getStore().getAt(rec).get('KLAreaStat_id') );
					}
					else fieldCmp.reset();
					break;
				}
				case 'KLSubRgn_id': {
					me.setValueAfterStoreLoad(
						fieldCmp,
						formParams.KLSubRgn_id,
						{ region_id: opts.region.number }
					);

					break;
				}
				case 'KLCity_id': {
					me.setValueAfterStoreLoad(
						fieldCmp,
						formParams.KLCity_id,
						{ subregion_id: ( formParams.KLSubRgn_id || opts.region.number ) }
					);
					break;
				}
				case 'KLTown_id': {
					me.setValueAfterStoreLoad(
						fieldCmp,
						formParams.KLTown_id,
						{ city_id: ( formParams.KLCity_id || formParams.KLSubRgn_id ) }
					);
					break;
				}
				case 'StreetAndUnformalizedAddressDirectory_id' : {
					me.setValueAfterStoreLoad(
						fieldCmp,
						formParams.StreetAndUnformalizedAddressDirectory_id,
						{ town_id: ( formParams.KLTown_id || formParams.KLCity_id) }
					);
					break;
				}
                default :{
                    //если в параметрах есть одноименный пункт со значением - значит это значение компонента
                    if(formParams && formParams[fieldName])
                        fieldCmp.setValue(formParams[fieldName]);

                    //заполнение временного континума
                    if(formParams && formParams[fieldName+'DT'])
                        fieldCmp.setValue(formParams[fieldName+'DT']);

                    break;
                }
            };
        };

        if(formParams.CmpCallCard_id){
            me.UslugaViewFrame.getGrid().getStore().load({
                params: {
                    CmpCallCard_id: formParams.CmpCallCard_id
                }
            });
        }
        else{
            me.UslugaViewFrame.getGrid().getStore().removeAll();
        }

    },

    //метод получения всех полей с компонента
    getAllFields: function(parentEl){
        var me = this,
            parentEl = parentEl || me.FormPanel.getForm(),
            fieldsTop = parentEl.items.items,
            allFields = [];

        var getAllFields = function(cmps){
            for(var i = 0; i < cmps.length; i++){
                allFields.push(cmps[i]);
                if(cmps[i].items && cmps[i].items.items.length){
                    getAllFields(cmps[i].items.items)
                };
            }
        };

        getAllFields(fieldsTop);

        return allFields;
    },

    getAllVAlues: function(parentEl){
		var me = this,
			parentEl = parentEl || null,
			fields = me.getAllFields(parentEl),
			values = {};

		for(var i = 0; i < fields.length; i++) {

			var fieldCmp = fields[i],
				fieldVal = fieldCmp.getValue(),
				fieldName = fieldCmp.getName();

			switch(true) {
				case ( fieldCmp.ownerCt.xtype == "swdatetimefield" ):{
					fieldVal = fieldCmp.getStringValue();
					values[fieldName] = fieldVal;
					break;
				}
				case ( fieldCmp.getXType && fieldCmp.getXType() == "swdatefield" ):{
					values[fieldName] = Ext.util.Format.date(fieldVal, 'd.m.Y');
					break;
				}
				case (fieldVal instanceof Date):
				{
					//просто дата пришла
					values[fieldName] = Ext.util.Format.date(fieldVal, 'd.m.Y H:i');
					break;
				}
				case ( fieldCmp.getXType && fieldCmp.getXType() == "checkbox" ):{
					values[fieldName] = fieldVal ? 2 : 1;
					break;
				}
				default : {
					values[fieldName] = fieldVal;
				}
			}

		}
		return values;
    },

    //инициализация грида услуг
    initUslugaViewframe: function(me){
        me.UslugaViewFrame = new sw.Promed.ViewFrame({
            object: 'CmpCallCardUsluga',
            dataUrl: '/?c=CmpCallCard&m=loadCmpCallCardUslugaGrid',
            height: 200,
            autoLoadData: false,
            border: true,
            useEmptyRecord: false,
            stringfields: [
                {name: 'CmpCallCardUsluga_id', type: 'int', header: 'ID', key: true},
                {name: 'CmpCallCard_id', type: 'int', hidden: true},
                {name: 'UslugaComplex_id', type: 'int', hidden: true},
                {name: 'MedPersonal_id', type: 'int', hidden: true},
                {name: 'MedStaffFact_id', type: 'int', hidden: true},
                {name: 'Person_id', type: 'int', hidden: true},
                {name: 'PayType_id', type: 'int', hidden: true},
                {name: 'UslugaCategory_id', type: 'int', hidden: true},
                {name: 'UslugaComplex_id', type: 'int', hidden: true},
                {name: 'UslugaComplexTariff_id', type: 'int', hidden: true},
                {name: 'CmpCallCardUsluga_setDate', type: 'string', header: 'Дата', width: 120},
                {name: 'CmpCallCardUsluga_setTime', type: 'string', header: 'Время', width: 120},
                {name: 'UslugaComplex_Code', type: 'string', header: 'Код', width: 160},
                {name: 'UslugaComplex_Name', type: 'string', header: 'Наименовение', id: 'autoexpand'},
                {name: 'UslugaComplexTariff_Name', type: 'int', header: 'Тариф'},
                {name: 'CmpCallCardUsluga_Kolvo', type: 'int', header: 'Количество'},
                {name: 'status', type: 'string', hidden: true}

            ],
            actions: [
                {name:'action_add', handler: function(){me.openCmpCallCardUslgugaEditWindow('add')}},
                {name:'action_edit', handler: function(){me.openCmpCallCardUslgugaEditWindow('edit')}},
                {name:'action_view', hidden: true, handler: function(){me.openCmpCallCardUslgugaEditWindow('view')}},
                {name:'action_delete', handler: function(){me.deleteCmpCallCardUslguga()}},
                {name:'action_refresh', hidden: true, disabled: true},
                {name:'action_print', hidden: true, disabled: true}
            ],
			onRowSelect: function(sm, rowIdx, record) {
				this.ViewActions.action_edit.setDisabled(false);
				this.ViewActions.action_delete.setDisabled(false);
			},
        });

        return me.UslugaViewFrame;
    },

    //открытие окна добавления/редактирования услуги
    openCmpCallCardUslgugaEditWindow: function(action) {
        var me = this;

        if (!action.inlist(['add','edit','view'])) {
            return;
        }

        var base_form = me.FormPanel.getForm();
        var grid = me.UslugaViewFrame.getGrid();
        var AcceptTime = Date.parseDate(base_form.findField('CmpCallCard_prmDT').getValue(),'d.m.Y H:i');

        var params = {
            action: action,
            CmpCallCard_setDT: AcceptTime,
            formParams: {}
        };

        switch (action) {

            case 'add':
                params.callback = function(){
                    return me.addCmpCallCardUslugaGridRec.apply(me,arguments);
                };
                break;

            case 'edit':
                params.callback = function(){
                    return me.editCmpCallCardUslugaGridRec.apply(me,arguments);
                };
                var record = grid.getSelectionModel().getSelected();
                if (!record || !record.get('CmpCallCardUsluga_id')) {
                    return false;
                }
                params.formParams = record.data;
                break;

            default:
                params.callback = Ext.emptyFn;
                break;
        }

        getWnd('swCmpCallCardUslugaEditWindow').show(params);

    },

    //Метод редактирования записи в гриде услуг
    editCmpCallCardUslugaGridRec: function(data) {

        if (!data.CmpCallCardUsluga_id) {
            return false;
        }

        var grid = this.UslugaViewFrame.getGrid(),
            rec_num = grid.getStore().find('CmpCallCardUsluga_id',data.CmpCallCardUsluga_id),
            rec = grid.getStore().getAt(rec_num);

        if (!rec) {
            return false;
        }

        for (var key in data) {
            if (data.hasOwnProperty(key)) {
                rec.set(key,data[key]);
            }
        }

        rec.set('status','edited');
        rec.commit();

    },

    //Метод добавления записи в грид услуг
    addCmpCallCardUslugaGridRec: function(data) {

        data.CmpCallCardUsluga_id = null;

        var rec = new Ext.data.Record(data);

        rec.set('status','added');

        rec.set('CmpCallCardUsluga_id',Math.floor(Math.random() * (-100000)));
        this.UslugaViewFrame.getGrid().getStore().add(rec);

    },

    //Метод удаления записи из грида услуг
    deleteCmpCallCardUslguga: function() {

        var grid = this.UslugaViewFrame.getGrid();

        var record = grid.getSelectionModel().getSelected();
        if (!record) {
            return;
        }

        grid.getStore().remove(record);
    },

    //номера вызова за день и за год
    getCmpCallCardNumber: function(me, form) {
        var url = '/?c=CmpCallCard&m=getCmpCallCardNumber',
            params = {};

        if(me.action == 'edit'){
            url = '/?c=CmpCallCard&m=existenceNumbersDayYear';
        }

        Ext.Ajax.request({
            callback: function(opt, success, response) {

                if ( success ) {
                    var response_obj = Ext.util.JSON.decode(response.responseText);

                    if(me.action == 'edit'){
                        form.findField('CmpCallCard_Ngod').setValue(response_obj.nextNumberYear);
                        form.findField('CmpCallCard_Numv').setValue(response_obj.nextNumberDay);
                    }else{
                        form.findField('CmpCallCard_Ngod').setValue(response_obj[0].CmpCallCard_Ngod);
                        form.findField('CmpCallCard_Numv').setValue(response_obj[0].CmpCallCard_Numv);

                        form.findField('CmpCallCard_Numv').focus(true);
                    }
                }
                else {
                    sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_opredelenii_nomera_vyizova'], function() {this.FormPanel.find('name', 'Person_SurName')[0].focus(true, 250);}.createDelegate(this) );
                }
            },
            url: url,
            params: params
        });
    },

    //ПАЦИЕНТ
    //поиск
    personSearch: function(){

        var me = this;

        if ( me.action == 'view' ) {
            return false;
        }

        var searchPersonWindow = getWnd('swPersonSearchWindow'),
            base_form = me.FormPanel.getForm(),
            personFirname = base_form.findField('Person_FirName').getValue(),
            personSecname = base_form.findField('Person_SecName').getValue(),
            personSurname = base_form.findField('Person_SurName').getValue();

        searchPersonWindow.show({
            onSelect: function(person_data) {
                searchPersonWindow.hide();
                me.setPersonData(person_data);
            },
            forObject: 'CmpCallCard',
            personFirname: personFirname,
            personSecname: personSecname,
            personSurname: personSurname,
            Person_Age: base_form.findField('Person_Age').getValue(),
            searchMode: 'all'
        });

        if( personFirname || personSecname || personSurname ){
            searchPersonWindow.doSearch();
        }
    },

    getPersonFields: function(){
        var personFieldset = this.FormPanel.find('name','personFieldset')[0];
        return this.getAllFields(personFieldset);
    },

    getAddressFields: function(){
        var addressFieldset = this.FormPanel.find('name','addressFieldset')[0];
        return this.getAllFields(addressFieldset);
    },

    setPersonData: function(data){
        var me = this,
			personFields = me.getPersonFields();

		if(me.personIsDead(data)){
			sw.swMsg.alert('Ошибка', 'Человек на дату приема вызова является умершим. Выбор невозможен', function() {});
			return false;
		}

        for(var index = 0; index < personFields.length; index++){
            var cmp = personFields[index],
                cmpName = cmp.name || cmp.hiddenName;

            if(cmpName){
                //all
                cmp.reset();
                if(cmp.isVisible()) cmp.disable();

                //byName
                switch (cmpName){
                    case 'Person_FirName': {
                        cmp.setValue(data.Person_Firname);
                        break;
                    }
                    case 'Person_SecName': {
                        cmp.setValue(data.Person_Secname);
                        break;
                    }
                    case 'Person_SurName': {
                        cmp.setValue(data.Person_Surname);
                        break;
                    }
                    case 'Person_Age_Inp':
                    case 'Person_Age': {
                        cmp.setValue(swGetPersonAge(data.Person_Birthday, new Date()));
                        break;
                    }
					case 'Person_BirthDay':{
						cmp.setValue(data.Person_Birthday);
						break;
					}
                }
            }
        }
    },

    personReset: function(){
        var personFields = this.getPersonFields();

        for(var index = 0; index < personFields.length; index++){
            var cmp = personFields[index],
                cmpName = cmp.name || cmp.hiddenName;

            if(cmpName){
                cmp.reset();
                if(cmp.isVisible()) cmp.enable();
            }
        }
    },

    personUnknown: function(){
        var personFields = this.getPersonFields();

        for(var index = 0; index < personFields.length; index++){
            var cmp = personFields[index],
                cmpName = cmp.name || cmp.hiddenName;

            if(cmpName && !cmpName.inlist(['Person_Age_Inp','AgeUnit_id'])){

                cmp.reset();
                if(cmp.isVisible()) cmp.disable();

                switch (cmpName){
                    case 'Person_FirName':
                    case 'Person_SecName':
                    case 'Person_SurName':
                    {
                        cmp.setValue(lang['neizvesten']);
                        break;
                    }
                }
            }
        }
    },

	//проверка на умершего
	personIsDead: function(data){
		var opts = getGlobalOptions(),
			deathDate = data.Person_deadDT,
			base_form = this.FormPanel.getForm();

		function addDays(date, days) {
			var result = new Date(date);
			result.setDate(result.getDate() + days);
			return result;
		}

		if(data.Person_IsDead == 'true' && deathDate){
			if(deathDate.length != '' && deathDate.length<=10)
				deathDate = new Date(deathDate.replace(/^(\d{2}).(\d{2}).(\d{4})/,'$3-$2-$1'));
			if(!Ext.isEmpty(opts.limit_days_after_death_to_create_call) && parseInt(opts.limit_days_after_death_to_create_call,10)>0)
				deathDate = addDays(deathDate,parseInt(opts.limit_days_after_death_to_create_call,10))

			if(deathDate <= new Date(base_form.findField('CmpCallCard_prmDT').getValue()) )
			{
				return true;
			}
		}
		return false;
	},

	//Расчет возраста на основе даты рождения
	//Устанавливает связку 4 полей: приема вызова, даты рождения, возраста b ед. возраста
	//editedField - редактируемое поле - от него зависит, в какую сторону будут заноситься изменения
	setPersonAgeFields: function(editedField) {
		var base_form = this.FormPanel.getForm(),
			prmDate = new Date(base_form.findField('CmpCallCard_prmDT').getValue()),//дата вызова
			birthdayField = base_form.findField('Person_BirthDay'),//дата рождения поле
			birthday = birthdayField.getValue(),//дата рождения значение
			Person_Age = base_form.findField('Person_Age'),//скрытый возраст в годах
			Person_Age_Inp = base_form.findField('Person_Age_Inp'),//возраст
			AgeUnit_id = base_form.findField('AgeUnit_id'),//ед. возраста
			date = new Date();

		switch (editedField){
			case 'CmpCallCard_prmDate':
			case 'Person_BirthDay': {
				if (Ext.isEmpty(birthday)) {
					Person_Age.setValue(null);
					Person_Age_Inp.setValue(null);
					AgeUnit_id.setValue('years');
				} else {
					var years = swGetPersonAge(birthday, prmDate);

					Person_Age.setValue(years);

					if (years > 0) {
						Person_Age_Inp.setValue(years);
						AgeUnit_id.setValue('years');
						Person_Age_Inp.maxValue = 120;
					} else {
						var days = Math.floor(Math.abs((prmDate - birthday)/(1000 * 3600 * 24)));
						var months = Math.floor(Math.abs(prmDate.getMonthsBetween(birthday)));

						if (months > 0) {
							Person_Age_Inp.setValue(months);
							AgeUnit_id.setValue('months');
							Person_Age_Inp.maxValue = 11;
						} else {
							Person_Age_Inp.setValue(days);
							AgeUnit_id.setValue('days');
							Person_Age_Inp.maxValue = 30;
						}
					}
				}
				break;
			}
			case 'Person_Age_Inp':
			case 'AgeUnit_id': {

				var inp = Person_Age_Inp.getValue(),
					type = AgeUnit_id.getValue(),
					calcBirthday;

				switch (type){
					case 'years': {
						if(!Ext.isEmpty(inp)){
							Person_Age.setValue( inp );
						}
						//calcBirthday = date.add(Date.YEAR, -inp);
						calcBirthday = new Date((date.getFullYear() - inp).toString());
						calcBirthday.setMonth(0);
						calcBirthday.setDate(1);
						Person_Age_Inp.maxValue = 120;
						break;
					}
					case 'months': {
						//calcBirthday = date.add(Date.MONTH, -inp);
						calcBirthday = new Date(date.setMonth(date.getMonth() - inp));
						calcBirthday.setDate(1);
						Person_Age_Inp.maxValue = 11;
						Person_Age.setValue(0);
						break;
					}
					case 'days': {
						//calcBirthday = date.add(Date.DAY, -inp);
						calcBirthday = new Date(date.setDate(date.getDate() - inp));
						Person_Age_Inp.maxValue = 30;
						Person_Age.setValue(0);
						break;
					}
				}
				Person_Age_Inp.validate();

				birthdayField.setValue(calcBirthday);

				break;
			}

			default: return;
		}
	},

	//АДРЕС
	// взаимосвязь адресных комбобоксов при редактировании
	// cmpChanged - измененный компонент
	// changedRecord - record измененного компонента
	setAddress: function(cmpChanged, changedRecord){
		var me = this,
			changedCmpName = cmpChanged.name || cmpChanged.hiddenName,
			addressFields = me.getAddressFields();

		for(var index = 0; index < addressFields.length; index++) {
			var cmp = addressFields[index],
				cmpName = cmp.name || cmp.hiddenName;

			if (cmpName) {
				switch(cmpName){
					//территория
					case 'KLAreaStat_idEdit': {
						//установка значения территории
						if(changedCmpName != cmpName){

							var rec = -1;

							switch(true){
								case ( !Ext.isEmpty(changedRecord.get('Town_id')) ) :{
									rec = cmp.getStore().find( 'KLTown_id', changedRecord.get('Town_id'), 0, false );
									break;
								}
								case ( !Ext.isEmpty(changedRecord.get('City_id')) ) :{
									rec = cmp.getStore().find( 'KLCity_id', changedRecord.get('City_id'), 0, false );
									break;
								}
								case ( !Ext.isEmpty(changedRecord.get('SubRGN_id')) ) :{
									rec = cmp.getStore().find( 'KLSubRGN_id', changedRecord.get('SubRGN_id'), 0, false );
									break;
								}
							};

							if(rec != -1) {
								cmp.setValue( cmp.getStore().getAt(rec).get('KLAreaStat_id') );
							}
							else cmp.reset();
						}
						break;
					}
					//район
					case 'KLSubRgn_id': {
						//поменялась территория и есть значение которое поставим
						if( changedCmpName.inlist(['KLAreaStat_idEdit']) ){
							me.setValueAfterStoreLoad(
								cmp,
								changedRecord.get('KLSubRGN_id'),
								{ region_id: changedRecord.get('KLRGN_id') }
							);
						}
						break;
					}
					//город
					case 'KLCity_id': {
						//поменялась территория или район
						if( changedCmpName.inlist(['KLAreaStat_idEdit', 'KLSubRgn_id']) ){
							var KLSubRGN_id = (
								changedRecord.get('SubRGN_id') //значение с комбика
								|| changedRecord.get('KLSubRGN_id') || changedRecord.get('KLRGN_id') //значение с территории
							);

							me.setValueAfterStoreLoad(
								cmp,
								changedRecord.get('KLCity_id'),
								{ subregion_id: KLSubRGN_id }
							);

						}
						break;
					}
					//населенный пункт
					case 'KLTown_id': {
						//поменялся район или город
						if(changedCmpName.inlist(['KLAreaStat_idEdit', 'KLSubRgn_id', 'KLCity_id'])){
							var city_id = (
								changedRecord.get('City_id') || changedRecord.get('SubRGN_id')//значения с комбиков
								|| changedRecord.get('KLCity_id') || changedRecord.get('KLSubRGN_id') ||  changedRecord.get('KLRGN_id')//значение с территории
							);
							me.setValueAfterStoreLoad(
								cmp,
								changedRecord.get('KLTown_id'),
								{ city_id: city_id }
							);
						}
						break;
					}
					//улица
					case 'StreetAndUnformalizedAddressDirectory_id': {
						//поменялся район или город или нас. пункт
						if(changedCmpName.inlist(['KLAreaStat_idEdit', 'KLSubRgn_id', 'KLCity_id', 'KLTown_id'])){
							var town_id = (
								changedRecord.get('SubRGN_id') || changedRecord.get('City_id') || changedRecord.get('Town_id') //поля с формы
								|| changedRecord.get('KLSubRGN_id') || changedRecord.get('KLCity_id') || changedRecord.get('KLTown_id') //поля комбика территория
							);

							me.setValueAfterStoreLoad(
								cmp,
								null,
								{ town_id: town_id }
							);
						}
						break;
					}
					case 'CmpCallCard_Dom': {
						break;
					}
					case 'CmpCallCard_Korp': {
						break;
					}
				}
			}
		}

	},

	//Вспомогательные функции

	//установка значения в комбик просле загрузки стора
	// cmp - компонент
	// val - значение
	// params - параметры загрузки
	// clb - возвратка
	setValueAfterStoreLoad: function(cmp, val, params, clb){
		var me = this,
			connection = cmp.getStore().proxy.getConnection(),
			transId = connection.transId ? connection.transId.tId : false,
			storeIsLoading = connection.isLoading(transId);

		cmp.getStore().load({
			params: params,
			callback: function(o, success){


				if(o && o.length){
					var record = this.findRecord(this.valueField, val);

					if(val && record){
						this.setValue(val);
						if(clb) clb(cmp, record);
					}
					else{
						this.setValue(null);
						if(clb) clb(cmp);
					}
				}
				else{
					this.getStore().removeAll();
					this.reset();
				}

			}.createDelegate(cmp)
		});


	},

    doSave: function(){
        var me = this,
			base_form = me.FormPanel.getForm(),
            values = me.getAllVAlues(),
            usluga_items = me.UslugaViewFrame.getGrid().getStore().query('CmpCallCardUsluga_id',/[^0]/).items,
            usluga_data_array = [],
            loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение карты вызова..."});

		if ( !base_form.isValid() ) {
			var error = ERR_INVFIELDS_MSG;
			me.action = 'edit';
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function(){
					var invalid = this.FormPanel.getInvalid()[0];
					if ( invalid ) {
						invalid.ensureVisible().focus();
					}
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: error,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		loadMask.show();
        for (var i = 0; i < usluga_items.length; i++) {
            usluga_data_array.push(usluga_items[i].data);
        };

        values.usluga_array = JSON.stringify(usluga_data_array);

        //карта вызова по санавиации
        values.CmpCallCardInputType_id = 3;
        //values.Person_Age = values.Person_Age_Inp;


        Ext.Ajax.request({
            url: '/?c=CmpCallCard&m=saveCmpCallCard',
            params: values,
            failure: function (response, opts) {
                loadMask.hide();
                sw.swMsg.alert(lang['oshibka'], action.result.Error_Msg);
            },
            callback: function (opt, success, response) {
                loadMask.hide();
                if (!success) {
                    sw.swMsg.alert(lang['oshibka'], action.result.Error_Msg);
                }

				var request = Ext.util.JSON.decode(response.responseText);

				if(request.success)
				{
					sw.swMsg.alert(langs('Сохранение'), langs('Талон вызова сохранён'), function(){
						me.hide();
					});
				};
            }
        })
    }

});
