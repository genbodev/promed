/**
 * Created with JetBrains PhpStorm.
 * User: Shorev
 * Date: 29.10.14
 * Time: 14:02
 * To change this template use File | Settings | File Templates.
 */

sw.Promed.swDrugRequestDopAddWindow = Ext.extend(sw.Promed.BaseForm,
    {
        autoHeight: true,
        objectName: 'swDrugRequestDopAddWindow',
        objectSrc: '/jscore/Forms/Polka/swDrugRequestDopAddWindow.js',
        title:lang['dopolnitelnaya_zayavka_dobavlenie'],
        layout: 'border',
        id: 'DRDA',
        modal: true,
        shim: false,
        resizable: false,
        maximizable: false,
        listeners:
        {
            hide: function()
            {
                this.onHide();
            }
        },
        onHide: Ext.emptyFn,
        width: 600,
        show: function()
        {
            sw.Promed.swDrugRequestDopAddWindow.superclass.show.apply(this, arguments);
            if(arguments[0].callback)
                this.callback = arguments[0].callback;
            var server_id = 0;
            var dt = new Date();
            var person_id = arguments[0].person_id;
            var medstafffact_id = arguments[0].medstafffact_id;
            var diag_id = arguments[0].diag_id;
            var recept_finance_id = arguments[0].recept_finance_id;
            this.recept_finance_id = recept_finance_id;
            this.person_id = person_id;
            var privilege_type_id = arguments[0].privilege_type_id;

            this.findById('DRDA_PersonInformationFrame').load({
                Person_id: person_id,
                Server_id: server_id
            });
            var base_form = this.findById('DRDA_mainPanel').getForm();

            var medstafffact_filter_params = {
                allowLowLevel: 'yes',
                isDlo: true,
                onDate: Ext.util.Format.date(dt, 'd.m.Y'),
                regionCode: getGlobalOptions().region.number
            };
            setMedStaffFactGlobalStoreFilter(medstafffact_filter_params);
            var med_staff_fact_combo = base_form.findField('MedStaffFact_id');
            //var med_staff_fact_record = med_staff_fact_combo.getStore().getById(medstafffact_id);
            var index = med_staff_fact_combo.getStore().findBy(function(rec) {
                if ( rec.get('MedStaffFact_id') == medstafffact_id ) {
                    return true;
                }
                else {
                    return false;
                }
            });
            if ( index >= 0 ) {
                med_staff_fact_combo.setValue(med_staff_fact_combo.getStore().getAt(index).get('MedStaffFact_id'));
            }
            med_staff_fact_combo.getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));


            var drugrequestperiod_comdo = this.findById('DRDA_DrugRequestPeriod_id');
            drugrequestperiod_comdo.getStore().each(function(rec) {
                if((Date.parseDate(rec.get('DrugRequestPeriod_begDate'), 'd.m.Y') <= dt) && (Date.parseDate(rec.get('DrugRequestPeriod_endDate'), 'd.m.Y') >=dt))
                {
                }
                else
                    drugrequestperiod_comdo.getStore().remove(rec);
            });
            drugrequestperiod_comdo.setValue(drugrequestperiod_comdo.getStore().getAt(0).get('DrugRequestPeriod_id'));
            drugrequestperiod_comdo.fireEvent('change', drugrequestperiod_comdo, drugrequestperiod_comdo.getValue(), null);

            this.findById('DRDA_MedStaffFactCombo').setValue(medstafffact_id);
            var evn_recept_set_date_field = this.findById('DRDA_setDate');
            evn_recept_set_date_field.setValue(dt);
            evn_recept_set_date_field.fireEvent('change', evn_recept_set_date_field, dt, null);

            var diag_field = this.findById('DRDA_Diag_id');
            diag_field.getStore().load({
                callback: function(records, options, success) {
                    diag_field.setValue(diag_id);

                }.createDelegate(this),
                params: {
                    Drug_id: diag_id
                }
            });

            var receptfinancecombo = this.findById('DRDA_ReceptFinance');
            receptfinancecombo.setValue(recept_finance_id);

            var privilegetypecombo = this.findById('DRDA_PrivilegeType');
            privilegetypecombo.getStore().load({
                params: {
                    date: dt,
                    Person_id: person_id
                },
                callback: function(records, options, success) {
                    privilegetypecombo.setValue(privilege_type_id);
                }.createDelegate(this)
            });

        },
        submit: function()
        {
			var base_form = this.findById('DRDA_mainPanel').getForm();

            var params = {
                DrugRequestDop_setDT        : Ext.util.Format.date(this.findById('DRDA_setDate').getValue(), 'Y-m-d'),//this.findById('DRDA_setDate').getValue(),
                DrugRequestPeriod_id        : this.findById('DRDA_DrugRequestPeriod_id').getValue(),
                MedStaffFact_id             : this.findById('DRDA_MedStaffFactCombo').getValue(),
                Diag_id                     : this.findById('DRDA_Diag_id').getValue(),
                DrugRequestDop_IsMedical    : this.findById('DRDA_IsMedical').getValue(),
                DrugFinance_id              : this.findById('DRDA_ReceptFinance').getValue(),
                PrivilegeType_id            : this.findById('DRDA_PrivilegeType').getValue(),
                DrugProtoMnn_id             : this.findById('DRDA_DrugProtoMnn_id').getValue(),
                Drug_id                     : this.findById('DRDA_Drug_id').getValue(),
                DrugRequestDop_PackCount    : this.findById('DRDA_PackCount').getValue(),
                Person_id                   : this.person_id
            };

			if ( !base_form.isValid() ) {
				sw.swMsg.show({
					buttons: Ext.Msg.OK,
					fn: function() {
						this.findById('DRDA_mainPanel').getFirstInvalidEl().focus(false);
					}.createDelegate(this),
					icon: Ext.Msg.WARNING,
					msg: ERR_INVFIELDS_MSG,
					title: ERR_INVFIELDS_TIT
				});
				return false;
			}

			Ext.Ajax.request({
				params: params,
				url: '?c=EvnRecept&m=saveDrugRequestDop',
				callback: function(options,success,response){
					var response_obj = Ext.util.JSON.decode(response.responseText);
					if(response_obj.success){
						sw.swMsg.show({
							buttons: Ext.Msg.OK,
							msg: lang['dopolnitelnaya_zayavka_uspeshno_sozdana']
						});
						this.hide();
					}
					else{
						sw.swMsg.show({
							buttons: Ext.Msg.OK,
							icon: Ext.Msg.ERROR,
							msg: (!Ext.isEmpty(response_obj.Error_Msg) ? response_obj.Error_Msg : lang['oshibka_sohraneniya_dopolnitelnoy_zayavki']),
							title: "Ошибка"
						});
					}
				}.createDelegate(this)
			});
        },
        initComponent: function()
        {
            var form = this;

            this.PersonPanel = new sw.Promed.FormPanel(
                {
                    region: 'center',
                    layout: 'form',
                    border: false,
                    frame: true,
                    style: 'padding: 10px;',
                    labelWidth: 90,
                    id: 'DRDA_PersonPanel',
                    items:
                        [
                            new sw.Promed.PersonInfoPanelView({
                                id: 'DRDA_PersonInformationFrame',
                                region: 'north'
                            })
                        ]
                });
            this.MainPanel = new sw.Promed.FormPanel(
                {
                    region: 'center',
                    layout: 'form',
                    border: false,
                    frame: true,
                    style: 'padding: 10px;',
                    labelWidth: 130,
                    id: 'DRDA_mainPanel',
                    labelAlign: 'left',
                    items:
                        [
                            {
                                allowBlank: false,
                                fieldLabel: lang['data'],
                                format: 'd.m.Y',
                                id: 'DRDA_setDate',
                                disabled: true,
                                plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
                                validateOnBlur: true,
                                xtype: 'swdatefield'
                            },
                            new sw.Promed.SwMedStaffFactGlobalCombo({
                                allowBlank: false,
                                disabled: true,
                                id: 'DRDA_MedStaffFactCombo',
                                lastQuery: '',
                                listWidth: 600,
                                tabIndex: TABINDEX_EREF + 7,
                                validateOnBlur: true,
                                width: 300
                            }),
                            {
                                allowBlank: false,
                                disabled: false,
                                id: 'DRDA_DrugRequestPeriod_id',
                                xtype: 'swdrugrequestperiodcombo',
                                width: 300,
                                listeners:
                                {
                                    change:
                                        function(combo,newValue)
                                        {
                                            var drugcombo = form.findById('DRDA_DrugProtoMnn_id');
                                            drugcombo.clearValue();
                                            drugcombo.getStore().removeAll();
                                            drugcombo.lastQuery = '';
                                            drugcombo.getStore().baseParams.ReceptFinance_id = form.recept_finance_id;
                                            drugcombo.getStore().baseParams.DrugProtoMnn_id = '';
                                            drugcombo.getStore().baseParams.DrugRequestPeriod_id = newValue;
                                            drugcombo.getStore().baseParams.query = '';
                                            if (newValue > 0)
                                            {
                                                drugcombo.getStore().load();
                                            }
                                        }
                                }
                            },
                            {
                                checkAccessRights: true,
                                allowBlank: false,
                                fieldLabel: lang['diagnoz'],
                                disabled: true,
                                hiddenName: 'Diag_id',
                                loadingText: lang['idet_poisk'],
                                id: 'DRDA_Diag_id',
                                listWidth: 600,
                                tabIndex: TABINDEX_EREF + 8,
                                validateOnBlur: true,
                                width: 300,
                                xtype: 'swdiagcombo'
                            },
                            new sw.Promed.SwYesNoCombo({
                                fieldLabel: lang['reshenie_vk'],
                                id:'DRDA_IsMedical',
                                tabIndex: TABINDEX_EVNRECSF + 75,
                                width: 100,
                                listeners: {
                                    'change': function(combo, newValue) {
                                        if(newValue==2){
                                            form.findById('DRDA_Drug_id').enable();
                                        }
                                        else{
                                            form.findById('DRDA_Drug_id').disable();
                                        }
                                    }
                                }
                            }),
                            {
                                allowBlank: false,
                                autoLoad: false,
                                comboSubject: 'ReceptFinance',
                                fieldLabel: lang['tip_finansirovaniya'],
                                hiddenName: 'ReceptFinance_id',
                                id: 'DRDA_ReceptFinance',
                                lastQuery: '',
                                listeners: {
                                    'change': function(combo, newValue, oldValue) {
                                    }
                                },
                                listWidth: 200,
                                tabIndex: TABINDEX_EREF + 9,
                                validateOnBlur: true,
                                disabled: true,
                                width: 200,
                                xtype: 'swcommonsprcombo'
                            },
                            {
                                allowBlank: false,
                                codeField: 'PrivilegeType_Code',
                                displayField: 'PrivilegeType_Name',
                                editable: false,
                                disabled: true,
                                fieldLabel: lang['kategoriya'],
                                hiddenName: 'PrivilegeType_id',
                                id: 'DRDA_PrivilegeType',
                                lastQuery: '',
                                listeners: {
                                    'change': function(combo, newValue, oldValue) {
                                    }.createDelegate(this)
                                },
                                store: new Ext.data.Store({
                                    autoLoad: false,
                                    reader: new Ext.data.JsonReader({
                                        id: 'PrivilegeType_id'
                                    }, [
                                        { name: 'PrivilegeType_Code', mapping: 'PrivilegeType_Code', type: 'int' },
                                        { name: 'PrivilegeType_id', mapping: 'PrivilegeType_id' },
                                        { name: 'PrivilegeType_Name', mapping: 'PrivilegeType_Name' },
                                        { name: 'ReceptDiscount_id', mapping: 'ReceptDiscount_id' },
                                        { name: 'ReceptFinance_id', mapping: 'ReceptFinance_id' },
                                        { name: 'PersonPrivilege_IsClosed', mapping: 'PersonPrivilege_IsClosed' },
                                        { name: 'PersonPrivilege_IsNoPfr', mapping: 'PersonPrivilege_IsNoPfr' },
                                        { name: 'PersonPrivilege_IsPersonDisp', mapping: 'PersonPrivilege_IsPersonDisp' },
                                        { name: 'PersonRefuse_IsRefuse', mapping: 'PersonRefuse_IsRefuse' }
                                    ]),
                                    url: C_PRIVCAT_LOAD_LIST
                                }),
                                tabIndex: TABINDEX_EREF + 12,
                                tpl: new Ext.XTemplate(
                                    '<tpl for="."><div class="x-combo-list-item">',
                                    '<table style="border: 0;"><tr><td style="width: 25px;"><font color="red">{PrivilegeType_Code}</font></td><td style="font-weight: {[ values.PersonPrivilege_IsClosed == 1 ? "bold" : "normal; color: red;" ]};">{PrivilegeType_Name}{[ values.PersonPrivilege_IsClosed == 1 ? "&nbsp;" : " (закрыта)" ]}</td></tr></table>',
                                    '</div></tpl>'
                                ),
                                validateOnBlur: true,
                                valueField: 'PrivilegeType_id',
                                width: 300,
                                xtype: 'swbaselocalcombo'
                            },
                            {
                                anchor: '100%',
                                allowBlank: false,
                                fieldLabel: lang['mnn'],
                                id: 'DRDA_DrugProtoMnn_id',
                                name: 'DrugProtoMnn_id',
                                xtype: 'swdrugprotomnnlistcombo',
                                tabIndex:4118,
                                loadingText: lang['idet_poisk'],
                                minLengthText: lang['pole_doljno_byit_zapolneno'],
                                queryDelay: 250,
                                listeners:
                                {
                                    'change': function(combo,newvalue){
                                        var IsMedical = form.findById('DRDA_IsMedical').getValue();
                                        if(2 == 2)
                                        {
                                            var DrugMnn_id = 0;
                                            var index = combo.getStore().findBy(function(rec) {
                                                if ( rec.get('DrugProtoMnn_id') == newvalue ) {
                                                    DrugMnn_id = rec.get('DrugMnn_id');
                                                    return true;
                                                }
                                            });
                                            var drug_combo = form.findById('DRDA_Drug_id');
                                            drug_combo.clearValue();
                                            drug_combo.getStore().removeAll();
                                            drug_combo.lastQuery = '';
                                            drug_combo.getStore().baseParams.DrugMnn_id = DrugMnn_id;
                                            drug_combo.getStore().baseParams.DopRequest = '2';
                                            // Если поле не пустое
                                            if ( newvalue > 0 ) {
                                                // загружаем список медикаментов
                                                drug_combo.getStore().load();
                                            }
                                        }
                                    }
                                }
                            },
                            {
                                allowBlank: true,
                                hiddenName: 'Drug_id',
                                id: 'DRDA_Drug_id',
                                disabled: true,
                                trigger2Class: 'hideTrigger',
                                listWidth: 800,
                                loadingText: lang['idet_poisk'],
                                minLengthText: lang['pole_doljno_byit_zapolneno'],
                                initComponent: function() {
                                    Ext.form.TwinTriggerField.prototype.initComponent.apply(this, arguments);

                                    this.store = new Ext.data.Store({
                                        autoLoad: false,
                                        reader: new Ext.data.JsonReader({
                                            id: 'Drug_id'
                                        }, [
                                            { name: 'Drug_id', type: 'int' },
                                            { name: 'Drug_Name', type: 'string' }
                                        ]),
                                        url: '/?c=EvnRecept&m=loadDrugList'
                                    });
                                },
                                tabIndex: TABINDEX_EREF + 20,
                                tpl: new Ext.XTemplate(
                                    '<tpl for="."><div class="x-combo-list-item">',
                                    '<td style="width: 70%;">{Drug_Name}&nbsp;</td>',
                                    '</tr></table>',
                                    '</div></tpl>'
                                ),
                                validateOnBlur: true,
                                width: 300,
                                xtype: 'swdrugcombo'
                            },
                            {
								allowBlank: false,
								allowNegative: false,
                                disabled: false,
                                fieldLabel: lang['kol-vo'],
                                name: 'DrugRequestDop_PackCount',
                                id: 'DRDA_PackCount',
                                xtype: 'numberfield'
                            }

                        ]
                }
                );
            Ext.apply(this,
                {
                    region: 'center',
                    layout: 'form',
                    buttons:
                        [{
                            text: lang['sohranit'],
                            id: 'lsqefOk',
                            iconCls: 'ok16',
                            handler: function() {
                                this.ownerCt.submit();
                            }
                        },{
                            text: '-'
                        },HelpButton(this),
                            {
                                iconCls: 'cancel16',
                                text: BTN_FRMCLOSE,
                                handler: function() {this.hide();}.createDelegate(this)
                            }],
                    items:
                        [
                            form.PersonPanel,
                            form.MainPanel
                        ]

                });
            sw.Promed.swDrugRequestDopAddWindow.superclass.initComponent.apply(this, arguments);
        }
    });