/**
 * swEvnUslugaStomByMesInputWindow - окно добавления выполнения стоматологических услуг по МЭС.
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package	Polka
 * @access	public
 * @version	23.07.2014
 * @comment	Префикс для id компонентов EUStomMS (EvnUslugaStomByMes)
 */
sw.Promed.swEvnUslugaStomByMesInputWindow = Ext.extend(sw.Promed.BaseForm, {
	buttonAlign: 'left',
	callback: Ext.emptyFn,
    Mse_Total: null,
    Mes_Spent: null,
	closable: false,
	closeAction: 'hide',
	collapsible: true,
    width: 900,
	draggable: true,
	formStatus: 'edit',
	height: 600,
	id: 'EvnUslugaStomByMesInputWindow',
    keys: [{
        alt: true,
        fn: function(inp, e) {
            var current_window = Ext.getCmp('EvnUslugaStomByMesInputWindow');
            switch (e.getKey()) {
                case Ext.EventObject.C:
                    current_window.doSave();
                    break;

                case Ext.EventObject.J:
                    current_window.hide();
                    break;

            }
        }.createDelegate(this),
        key: [
            Ext.EventObject.C,
            Ext.EventObject.G,
            Ext.EventObject.J,
            Ext.EventObject.NUM_ONE,
            Ext.EventObject.NUM_TWO,
            Ext.EventObject.ONE,
            Ext.EventObject.TWO
        ],
        stopEvent: true
    }, {
        alt: false,
        fn: function(inp, e) {
            var current_window = Ext.getCmp('EvnUslugaStomByMesInputWindow');
            switch (e.getKey()) {
                case Ext.EventObject.ESC:
                    current_window.hide();
                    break;
            }
        },
        key: [
            Ext.EventObject.ESC
        ],
        stopEvent: true
    }],
    layout: 'border',
    maximizable: true,
    minHeight: 450,
    minWidth: 900,
    modal: true,
    onHide: Ext.emptyFn,
    plain: true,
    resizable: true,
    listeners: {
        'hide': function(win) {
            win.onHide();
        },
        'maximize': function(win) {
            win.findById('EUStomMS_EvnUslugaStomPanel').doLayout();
        },
        'restore': function(win) {
            win.findById('EUStomMS_EvnUslugaStomPanel').doLayout();
        }
    },
	initComponent: function() {
		var form = this;

        form.uslugaPanel = new sw.Promed.UslugaSelectPanelByMes({
			defaultCheck: false,
            id: form.getId() + 'UslugaSelectPanel',
            evnClassSysNick: 'EvnUslugaStom',
            getBaseForm: function()
            {
                if (!this._baseForm) {
                    this._baseForm = form.formPanel.getForm();
                }
                return this._baseForm;
            },
            isDisableUem: function()
            {
                return Ext.isEmpty(this.getBaseForm().findField('MedStaffFact_sid').getValue());
            },
			isAllowEmptyUed: function() {
				return !Ext.isEmpty(this.getBaseForm().findField('EvnDiagPLStom_id').getValue());
			},
            isDisableUed: function()
            {
                return Ext.isEmpty(this.getBaseForm().findField('MedStaffFact_id').getValue());
            },
            getEvnUslugaSummaField: function()
            {
                return this.getBaseForm().findField('EvnUslugaStom_Summa');
            },
            getEvnUslugaUEDField: function()
            {
                return this.getBaseForm().findField('EvnUslugaStom_UED');
            },
            getEvnUslugaUEMField: function()
            {
                return this.getBaseForm().findField('EvnUslugaStom_UEM');
            }
        });

        form.personInfoPanel = new sw.Promed.PersonInformationPanelShort({
            id: 'EUStomMS_PersonInformationFrame',
            region: 'north'
        });

        form.formPanel = new Ext.form.FormPanel({
            autoScroll: true,
            bodyBorder: false,
            bodyStyle: 'padding: 5px 5px 0',
            border: false,
            frame: false,
            id: 'EvnUslugaStomByMes',
            labelAlign: 'right',
            labelWidth: 130,
            layout: 'form',
            reader: new Ext.data.JsonReader({
                success: Ext.emptyFn
            }, [
                { name: 'accessType' },
                { name: 'EvnUslugaStom_id' },
                { name: 'EvnUslugaStom_rid' },
                { name: 'EvnUslugaStom_pid' },
                { name: 'EvnDiagPLStom_id' },
                { name: 'Person_id' },
                { name: 'PersonEvn_id' },
                { name: 'Server_id' },
                { name: 'EvnUslugaStom_setDate' },
                { name: 'LpuSection_uid' },
                { name: 'MedPersonal_id' },
                { name: 'MedPersonal_sid' },
                { name: 'EvnUslugaStom_UED' },
                { name: 'EvnUslugaStom_UEM' },
                { name: 'PayType_id' },
                { name: 'EvnUslugaStom_Summa' }
            ]),
            region: 'center',
            url: '/?c=EvnUsluga&m=saveEvnUslugaStom',
            items: [{
                name: 'accessType',
                value: '',
                xtype: 'hidden'
            }, {
                name: 'EvnUslugaStom_id',
                value: 0,
                xtype: 'hidden'
            }, {
                name: 'EvnUslugaStom_rid',
                value: 0,
                xtype: 'hidden'
            }, {
                name: 'EvnUslugaStom_pid',
                value: 0,
                xtype: 'hidden'
            }, {
                name: 'EvnDiagPLStom_id',
                value: 0,
                xtype: 'hidden'
            }, {
                name: 'MedPersonal_id',
                value: 0,
                xtype: 'hidden'
            }, {
                name: 'MedPersonal_sid',
                value: 0,
                xtype: 'hidden'
            }, {
                name: 'Person_id',
                value: 0,
                xtype: 'hidden'
            }, {
                name: 'PersonEvn_id',
                value: 0,
                xtype: 'hidden'
            }, {
                name: 'Server_id',
                value: -1,
                xtype: 'hidden'
            },
                new sw.Promed.Panel({
                    autoHeight: true,
                    // bodyStyle: 'padding: 0.5em;',
                    border: true,
                    collapsible: true,
                    id: 'EUStomMS_EvnUslugaStomPanel',
                    layout: 'form',
                    style: 'margin-bottom: 0.5em;',
                    title: lang['1_usluga'],
                    items: [{
                        allowBlank: false,
                        disabled: true,
                        fieldLabel: lang['data_vyipolneniya'],
                        format: 'd.m.Y',
                        listeners: {
                            'change': function(field, newValue, oldValue) {
                                if ( blockedDateAfterPersonDeath('personpanelid', 'EUStomMS_PersonInformationFrame', field, newValue, oldValue) )
                                    return false;

                                var bf = this.formPanel.getForm();

                                var lpu_section_id = bf.findField('LpuSection_uid').getValue();
                                var med_staff_fact_id = bf.findField('MedStaffFact_id').getValue();
                                var med_staff_fact_sid = bf.findField('MedStaffFact_sid').getValue();

                                bf.findField('LpuSection_uid').clearValue();
                                bf.findField('MedStaffFact_id').clearValue();
                                bf.findField('MedStaffFact_sid').clearValue();

                                var lpuSectionFilters = {
									allowLowLevel: 'yes',
                                    isStom: true,
                                    regionCode: getGlobalOptions().region.number
                                };

                                var medStaffFactFilters = {
									allowLowLevel: 'yes',
                                    isStom: true,
                                    regionCode: getGlobalOptions().region.number
                                };

                                var midMedStaffFactFilters = {
                                    isStom: true,
                                    isMidMedPersonal: true,
                                    regionCode: getGlobalOptions().region.number
                                };

                                if ( !Ext.isEmpty(newValue) ) {
                                    lpuSectionFilters.onDate = Ext.util.Format.date(newValue, 'd.m.Y');
                                    medStaffFactFilters.onDate = Ext.util.Format.date(newValue, 'd.m.Y');
                                    midMedStaffFactFilters.onDate = Ext.util.Format.date(newValue, 'd.m.Y');
                                    //bf.findField('UslugaComplex_id').getStore().baseParams.UslugaComplex_Date = (typeof newValue == 'object' ? Ext.util.Format.date(newValue, 'd.m.Y') : newValue);
                                    //bf.findField('UslugaComplex_id').getStore().removeAll();
                                }

                                setLpuSectionGlobalStoreFilter(lpuSectionFilters);
                                setMedStaffFactGlobalStoreFilter(medStaffFactFilters);

								bf.findField('LpuSectionProfile_id').onChangeDateField(field, newValue);
                                bf.findField('LpuSection_uid').getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));
                                bf.findField('MedStaffFact_id').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));

                                setMedStaffFactGlobalStoreFilter(midMedStaffFactFilters);

                                bf.findField('MedStaffFact_sid').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));

                                if ( bf.findField('LpuSection_uid').getStore().getById(lpu_section_id) ) {
                                    bf.findField('LpuSection_uid').setValue(lpu_section_id);
                                }

                                if ( bf.findField('MedStaffFact_id').getStore().getById(med_staff_fact_id) ) {
                                    bf.findField('MedStaffFact_id').setValue(med_staff_fact_id);
                                }

                                if ( bf.findField('MedStaffFact_sid').getStore().getById(med_staff_fact_sid) ) {
                                    bf.findField('MedStaffFact_sid').setValue(med_staff_fact_sid);
                                }
                            }.createDelegate(this)
                        },
                        name: 'EvnUslugaStom_setDate',
                        plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
                        width: 100,
                        xtype: 'swdatefield'
                    }, {
                        autoHeight: true,
                        style: 'padding: 2px 0px 0px 0px;',
                        xtype: 'fieldset',
                        items: [{
                            allowBlank: false,
                            disabled: true,
                            hiddenName: 'UslugaPlace_id',
                            value: 1,
                            width: 500,
                            xtype: 'swuslugaplacecombo'
                        }, {
                            allowBlank: false,
                            hiddenName: 'LpuSection_uid',
                            id: 'EUStomMS_LpuSectionCombo',
                            lastQuery: '',
                            linkedElements: [
                                'EUStomMS_MedPersonalCombo'
                            ],
                            width: 500,
                            xtype: 'swlpusectionglobalcombo'
                        }, {
							hiddenName: 'LpuSectionProfile_id',
							hidden: true,
							lastQuery: '',
							width: 500,
							xtype: 'swlpusectionprofilewithfedcombo'
						}]
                    }, {
                        autoHeight: true,
                        style: 'padding: 2px 0px 0px 0px;',
                        title: lang['vrach_vyipolnivshiy_uslugu'],
                        xtype: 'fieldset',
                        items: [{
                            allowBlank: false,
                            fieldLabel: lang['kod_i_fio_vracha'],
                            hiddenName: 'MedStaffFact_id',
                            id: 'EUStomMS_MedPersonalCombo',
                            lastQuery: '',
                            listWidth: 750,
                            parentElementId: 'EUStomMS_LpuSectionCombo',
                            width: 500,
                            xtype: 'swmedstafffactglobalcombo'
                        }, {
                            border: false,
                            hidden: getRegionNick().inlist(['perm','vologda']),
                            layout: 'form',
                            items: [{
                                allowBlank: true,
                                fieldLabel: lang['sred_m_personal'],
                                hiddenName: 'MedStaffFact_sid',
                                id: 'EUStomMS_MidMedPersonalCombo',
                                lastQuery: '',
                                listWidth: 750,
                                width: 500,
                                xtype: 'swmedstafffactglobalcombo'
                            }]
                        }]
                    }, {
                        allowBlank: false,
                        hiddenName: 'PayType_id',
                        listeners: {
                            'change': function (combo, newValue, oldValue) {
                                this.loadUslugaPanel();
                            }.createDelegate(this)
                        },
                        width: 250,
                        xtype: 'swpaytypecombo'
                    }, {
                        allowBlank: false,
                        allowDecimals: true,
                        allowNegative: false,
                        fieldLabel: lang['uet_vracha'],
                        name: 'EvnUslugaStom_UED',
                        width: 100,
                        xtype: 'numberfield'
                    }, {
                        border: false,
                        hidden: getRegionNick().inlist(['perm','vologda']),
                        layout: 'form',
                        items: [{
                            allowBlank: true,
                            allowDecimals: true,
                            allowNegative: false,
                            fieldLabel: lang['uet_sred_m_p'],
                            name: 'EvnUslugaStom_UEM',
                            width: 100,
                            xtype: 'numberfield'
                        }]
                    }, {
                        allowDecimals: true,
                        allowNegative: false,
                        disabled: true,
                        fieldLabel: lang['summa_uet'],
                        name: 'EvnUslugaStom_Summa',
                        width: 100,
                        xtype: 'numberfield'
					}, {
						border: false,
						hidden: !getRegionNick().inlist(['perm','vologda']),
						layout: 'form',
						items: [{
							boxLabel: 'С учетом заведенных услуг в рамках случая',
							labelSeparator: '',
							listeners: {
								'check': function(checkbox, value) {
									if ( !getRegionNick().inlist(['perm','vologda']) ) {
										return false;
									}

									this.loadUslugaPanel();
								}.createDelegate(this)
							},
							name: 'doNotIncludeEvnUslugaDid',
							xtype: 'checkbox'
						}]
                    }]
                }),
                form.uslugaPanel
            ]
        });

		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.doSave();
				}.createDelegate(this),
				iconCls: 'save16',
				onShiftTabAction: function () {
				},
				onTabAction: function () {
				},
				text: BTN_FRMSAVE
			}, {
				text: '-'
			},
			HelpButton(this, -1),
			{
				handler: function() {
					this.hide();
				}.createDelegate(this),
				iconCls: 'cancel16',
				onShiftTabAction: function () {
				},
				onTabAction: function () {
					//
				},
				text: BTN_FRMCANCEL
			}],
			items: [ form.personInfoPanel, form.formPanel ]
		});

		sw.Promed.swEvnUslugaStomByMesInputWindow.superclass.initComponent.apply(this, arguments);

		this.findById('EUStomMS_LpuSectionCombo').addListener('change', function(combo, newValue, oldValue) {
            this.loadUslugaPanel();
			var base_form = this.formPanel.getForm();
			if (base_form.findField('UslugaPlace_id').getValue() == 1 && !Ext.isEmpty(newValue)) {
				base_form.findField('LpuSectionProfile_id').setValue(combo.getFieldValue('LpuSectionProfile_id'));
			}
			else {
				base_form.findField('LpuSectionProfile_id').setValue(null);
			}
			base_form.findField('LpuSectionProfile_id').onChangeLpuSectionId(combo, newValue);
		}.createDelegate(this));

		this.findById('EUStomMS_MidMedPersonalCombo').addListener('change', function(combo, newValue, oldValue) {
			var bf = this.formPanel.getForm();
			if ( Ext.isEmpty(newValue) ) {
				bf.findField('EvnUslugaStom_UEM').setValue('');
			}
		}.createDelegate(this));

		this.findById('EUStomMS_LpuSectionCombo').addListener('keydown', function(inp, e) {
			if (e.getKey() == Ext.EventObject.TAB && e.shiftKey == true) {
				e.stopEvent();
				this.buttons[this.buttons.length - 1].focus();
			}
		}.createDelegate(this));
	},
    doSave: function(options) {
        // options @Object
        // options.openChildWindow @Function Открыть дочернее окно после сохранения
        if ( this.formStatus == 'save' ) {
            return false;
        }

		options = options || {};

        this.formStatus = 'save';

        var bf = this.formPanel.getForm();

        if ( !bf.isValid() ) {
            sw.swMsg.show({
                buttons: Ext.Msg.OK,
                fn: function() {
                    this.formStatus = 'edit';
                    this.formPanel.getFirstInvalidEl().focus(true);
                }.createDelegate(this),
                icon: Ext.Msg.WARNING,
                msg: ERR_INVFIELDS_MSG,
                title: ERR_INVFIELDS_TIT
            });
            return false;
        }

        var med_staff_fact_id = bf.findField('MedStaffFact_id').getValue();
        var med_staff_fact_sid = bf.findField('MedStaffFact_sid').getValue();
        var params = new Object();
        var PayType_SysNick = '';

        var i, index, j, record;

        // MedPersonal_id
        index = bf.findField('MedStaffFact_id').getStore().findBy(function(rec) {
            return (rec.get('MedStaffFact_id') == med_staff_fact_id);
        });

        if ( index >= 0 ) {
            bf.findField('MedPersonal_id').setValue(bf.findField('MedStaffFact_id').getStore().getAt(index).get('MedPersonal_id'));
        }

        // MedPersonal_sid
        index = bf.findField('MedStaffFact_sid').getStore().findBy(function(rec) {
            return (rec.get('MedStaffFact_id') == med_staff_fact_sid);
        });

        if ( index >= 0 ) {
            bf.findField('MedPersonal_sid').setValue(bf.findField('MedStaffFact_sid').getStore().getAt(index).get('MedPersonal_id'));
        }

        // Вид оплаты
        index = bf.findField('PayType_id').getStore().findBy(function(rec) {
            return (rec.get('PayType_id') == bf.findField('PayType_id').getValue());
        });

        if ( index >= 0 ) {
            PayType_SysNick = bf.findField('PayType_id').getStore().getAt(index).get('PayType_SysNick');
        }

        params.EvnUslugaStom_IsMes = 2;
        params.EvnUslugaStom_setDate = Ext.util.Format.date(bf.findField('EvnUslugaStom_setDate').getValue(), 'd.m.Y');
        params.LpuSection_uid = bf.findField('LpuSection_uid').getValue();
        params.LpuSectionProfile_id = bf.findField('LpuSectionProfile_id').getValue();
        params.UslugaPlace_id = bf.findField('UslugaPlace_id').getValue();

        var me = this;
        var uslugaErr = me.uslugaPanel.validateUslugaSelectedList();
        if (uslugaErr) {
            me.formStatus = 'edit';
            sw.swMsg.alert(lang['oshibka'], uslugaErr);
            return false;
        }
        params.UslugaSelectedList = me.uslugaPanel.getUslugaSelectedList(true);
		params.ignoreParentEvnDateCheck = (!Ext.isEmpty(options.ignoreParentEvnDateCheck) && options.ignoreParentEvnDateCheck === 1) ? 1 : 0;
		params.ignoreKSGCheck = (!Ext.isEmpty(options.ignoreKSGCheck) && options.ignoreKSGCheck === 1) ? 1 : 0;

        var loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT_SAVE });
        loadMask.show();
        bf.submit({
            failure: function(result_form, action) {
                me.formStatus = 'edit';
                loadMask.hide();
                if (action.result) {
                    if (action.result.Error_Msg) {
                        sw.swMsg.alert(lang['oshibka'], action.result.Error_Msg);
                    } else {
                        sw.swMsg.alert(langs('Ошибка'), langs('При сохранении произошли ошибки [Тип ошибки: 1]'));
                    }
                }
            },
            params: params,
            success: function(result_form, action) {
                me.formStatus = 'edit';
                loadMask.hide();
                var onEvnUslugaStomSave = function() {
                    if ( options && typeof options.openChildWindow == 'function' ) {
                        options.openChildWindow();
                    } else {
                        var data = {};

                        data.evnUslugaData = {
                            'accessType': 'edit',
                            'EvnClass_SysNick': 'EvnUslugaStom',
                            'EvnUsluga_id': bf.findField('EvnUslugaStom_id').getValue(),
                            'EvnUsluga_setDate': bf.findField('EvnUslugaStom_setDate').getValue(),
                            'PayType_id': bf.findField('PayType_id').getValue(),
                            'PayType_SysNick': PayType_SysNick
                        };

                        me.callback(data);
                        me.hide();
                    }
                };

				if ( !action.result ) {
                    sw.swMsg.alert(langs('Ошибка'), langs('При сохранении произошли ошибки [Тип ошибки: 2]'));
					return false;
				}

                if ( action.result.EvnUslugaStom_id > 0 ) {
                    bf.findField('EvnUslugaStom_id').setValue(action.result.EvnUslugaStom_id);
                    onEvnUslugaStomSave();
					return true;
                }

				if ( !Ext.isEmpty(action.result.Alert_Msg) ) {
					sw.swMsg.show({
						buttons: Ext.Msg.YESNO,
						fn: function(buttonId, text, obj) {
							if ( buttonId == 'yes' ) {
								if (action.result.Error_Code == 109) {
									options.ignoreParentEvnDateCheck = 1;
								}

								if (action.result.Error_Code == 110) {
									options.ignoreKSGCheck = 1;
								}

								me.doSave(options);
							}
						},
						icon: Ext.MessageBox.QUESTION,
						msg: action.result.Alert_Msg,
						title: 'Продолжить сохранение?'
					});
				}
				else {
					sw.swMsg.alert('Ошибка', !Ext.isEmpty(action.result.Error_Msg) ? action.result.Error_Msg : 'При сохранении произошли ошибки [Тип ошибки: 3]');
				}
			}
        });
    },
    loadUslugaPanel: function () {
        var loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT }),
            bf = this.formPanel.getForm(),
            params = {
                LpuSection_id: bf.findField('LpuSection_uid').getValue()
                ,PayType_id: bf.findField('PayType_id').getValue()
                ,Person_id: bf.findField('Person_id').getValue()
                ,UEDAboveZero: (getRegionNick() == 'perm' ? 1 : null)
                ,UslugaComplexTariff_Date: bf.findField('EvnUslugaStom_setDate').getValue()
				,doNotIncludeEvnUslugaDid: (bf.findField('doNotIncludeEvnUslugaDid').getValue() === true ? 1 : null)
            };
        this.uslugaPanel.setTariffParams(params);
        loadMask.show();
        this.uslugaPanel.doLoad({
            callback: function(){
                loadMask.hide();
            }
        });
    },

	show: function() {
		sw.Promed.swEvnUslugaStomByMesInputWindow.superclass.show.apply(this, arguments);

		this.findById('EUStomMS_EvnUslugaStomPanel').expand();
		this.restore();
		this.center();
        this.callback = Ext.emptyFn;
        this.formStatus = 'edit';
        this.onHide = Ext.emptyFn;

        if ( !arguments[0] || !arguments[0].formParams || !arguments[0].Mes_id ) {
            sw.swMsg.alert(lang['soobschenie'], lang['nevernyie_parametryi'], function() {
                this.hide();
            }.createDelegate(this));
            return false;
        }
        this.Mes_id = arguments[0].Mes_id;
        this.callback = arguments[0].callback || Ext.emptyFn;
        this.formMode = arguments[0].formMode || 'classic';
        this.onHide = arguments[0].onHide || Ext.emptyFn;

		if ( this.formMode == 'morbus' && getRegionNick() != 'ekb' ) {
			this.uslugaPanel.ViewGridPanel.setTitle(lang['uslugi_po_ksg']);
		}
		else {
			this.uslugaPanel.ViewGridPanel.setTitle(lang['uslugi_po_mes']);
		}

		var bf = this.formPanel.getForm();
        var set_date_field = bf.findField('EvnUslugaStom_setDate');
        var lpu_section_combo = bf.findField('LpuSection_uid');
        var med_personal_combo = bf.findField('MedStaffFact_id');
        var pay_type_combo = bf.findField('PayType_id');
		bf.reset();
        bf.findField('EvnUslugaStom_UEM').setDisabled(true);
        bf.findField('EvnUslugaStom_UED').setDisabled(true);
        this.uslugaPanel.doReset();
        this.findById('EUStomMS_EvnUslugaStomPanel').isLoaded = true;
		bf.setValues(arguments[0].formParams);
        this.uslugaPanel.applyParams(bf.findField('EvnUslugaStom_pid').getValue(), this.Mes_id, bf.findField('EvnUslugaStom_rid').getValue(), bf.findField('EvnDiagPLStom_id').getValue());
		this.personInfoPanel.load({
			Person_id: (arguments[0].Person_id ? arguments[0].Person_id : ''),
			Person_Birthday: (arguments[0].Person_Birthday ? arguments[0].Person_Birthday : ''),
			Person_Firname: (arguments[0].Person_Firname ? arguments[0].Person_Firname : ''),
			Person_Secname: (arguments[0].Person_Secname ? arguments[0].Person_Secname : ''),
			Person_Surname: (arguments[0].Person_Surname ? arguments[0].Person_Surname : ''),
			callback: function() {
				clearDateAfterPersonDeath('personpanelid', 'EUStomMS_PersonInformationFrame', set_date_field);
			}
		});

        this.setTitle(lang['vyipolnenie_stomatologicheskih_uslug_po'] + ((this.formMode == 'morbus' && getRegionNick() != 'ekb') ? lang['ksg'] : lang['mes']));

        if (set_date_field.getValue()) {
            set_date_field.fireEvent('change', set_date_field, set_date_field.getValue());
        }
        if ( arguments[0].formParams.PayType_id ) {
            pay_type_combo.setValue(arguments[0].formParams.PayType_id);
        }
        if ( arguments[0].formParams.LpuSection_uid ) {
            lpu_section_combo.setValue(arguments[0].formParams.LpuSection_uid);
            lpu_section_combo.fireEvent('change', lpu_section_combo, arguments[0].formParams.LpuSection_uid);
            lpu_section_combo.disable();
        }
        if ( arguments[0].formParams.MedStaffFact_id ) {
            med_personal_combo.setValue(arguments[0].formParams.MedStaffFact_id);
        }
				
		if (arguments[0].formParams && arguments[0].formParams && arguments[0].formParams.LpuSectionProfile_id) {
			var lpu_section_profile_id = arguments[0].formParams.LpuSectionProfile_id;
			setTimeout(function(){
				bf.findField('LpuSectionProfile_id').setValue(lpu_section_profile_id);
			}, 250);
		}

        bf.clearInvalid();
        pay_type_combo.focus(true, 250);
        return true;
	}
});
