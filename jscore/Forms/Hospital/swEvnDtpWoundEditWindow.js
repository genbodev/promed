/**
 * swEvnDtpWoundEditWindow - окно редактирования/добавления/просмотра извещения о раненом в ДТП.
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/projects/promed
 *
 * @package      Hospital
 * @access       public
 * @copyright    Copyright (c) 2009-2010 Swan Ltd.
 * @author       Alexander "Alf" Arefyev (avaref@gmail.com)
 * @version      02.04.2010
 * @comment      Префикс для id компонентов EDWEW (swEvnDtpWoundEditWindow)
 *
 */
sw.Promed.swEvnDtpWoundEditWindow = Ext.extend(sw.Promed.BaseForm, {
    action: null,
    buttonAlign: 'left',
    callback: Ext.emptyFn,
    closeAction: 'hide',
    formStatus: 'edit',
    layout: 'border',
    maximized: true,
    iconCls: 'stac-accident-injured16',
    title: lang['izveschenie_o_ranenom_v_dtp'],
    id: 'EvnDtpWoundEditWindow',

    /**
	 * Отмена
	 */
    onCancelAction: function() {
        this.hide();
    },
	
    /**
	 * Проверка данных перед сохранением
	 */
    validateForm: function() {
        this.error_msg = null;
        this.focus_element = null;
		
        var error_msg = new Array();
        var invalid_elements = new Array();
        var form = this.frameForm.getForm();
		
        if (!form.isValid()) {
            error_msg.push(ERR_INVFIELDS_MSG);
            invalid_elements.push(this.frameForm.getFirstInvalidEl());
        }
		
        var cur_date = new Date();
        var set_date = form.findField('EvnDtpWound_setDate').getValue();
        var obr_date = form.findField('EvnDtpWound_ObrDate').getValue();
        var hosp_date = form.findField('EvnDtpWound_HospDate').getValue();
        var dtp_date = form.findField('EvnDtpWound_DtpDate').getValue();
        var other_lpu_date = form.findField('EvnDtpWound_OtherLpuDate').getValue();
        var person_birthday = this.framePersonInfo.getFieldValue('Person_Birthday');
		
        if (dtp_date > cur_date) {
            error_msg.push(lang['data_dtp_ne_mojet_byit_bolshe_tekuschey_datyi']);
            invalid_elements.push(form.findField('EvnDtpWound_DtpDate'));
        }
        if (obr_date > cur_date) {
            error_msg.push(lang['data_obrascheniya_ne_mojet_byit_bolshe_tekuschey_datyi']);
            invalid_elements.push(form.findField('EvnDtpWound_ObrDate'));
        }
        if (hosp_date && hosp_date > cur_date) {
            error_msg.push(lang['data_gospitalizatsii_ne_mojet_byit_bolshe_tekuschey_datyi']);
            invalid_elements.push(form.findField('EvnDtpWound_ObrDate'));
        }
        if (set_date && set_date > cur_date) {
            error_msg.push(lang['data_sostavleniya_izvescheniya_ne_mojet_byit_bolshe_tekuschey_datyi']);
            invalid_elements.push(form.findField('EvnDtpWound_setDate'));
        }
        if (other_lpu_date && other_lpu_date > cur_date) {
            error_msg.push(lang['data_perevoda_v_drugoe_lpu_ne_mojet_byit_bolshe_tekuschey_datyi']);
            invalid_elements.push(this.focus_element = form.findField('EvnDtpWound_OtherLpuDate'));
        }
        if (person_birthday) {
            if (dtp_date < person_birthday) {
                error_msg.push(lang['data_dtp_ne_mojet_byit_menshe_datyi_rojdeniya']);
                invalid_elements.push(form.findField('EvnDtpWound_DtpDate'));
            }
            if (obr_date < person_birthday) {
                error_msg.push(lang['data_obrascheniya_v_lpu_ne_mojet_byit_menshe_datyi_rojdeniya']);
                invalid_elements.pus(form.findField('EvnDtpWound_ObrDate'));
            }
            if (hosp_date && hosp_date < person_birthday) {
                error_msg.push(lang['data_gospitalizatsii_ne_mojet_byit_menshe_datyi_rojdeniya']);
                invalid_elements.push(form.findField('EvnDtpWound_HospDate'));
            }
            if (set_date && set_date < person_birthday) {
                error_msg.push(lang['data_sostavleniya_izvescheniya_ne_mojet_byit_menshe_datyi_rojdeniya']);
                invalid_elements.push(form.findField('EvnDtpWound_setDate'));
            }
            if (other_lpu_date && other_lpu_date < person_birthday) {
                error_msg.push(lang['data_perevoda_v_drugoe_lpu_ne_mojet_byit_menshe_datyi_rojdeniya']);
                invalid_elements.push(form.findField('EvnDtpWound_OtherLpuDate'));
            }
        }
        if (obr_date < dtp_date) {
            error_msg.push(lang['data_obrascheniya_ne_mojet_byit_menshe_datyi_dtp']);
            invalid_elements.push(form.findField('EvnDtpWound_ObrDate'));
        }
        if (hosp_date && hosp_date < dtp_date) {
            error_msg.push(lang['data_gospitalizatsii_ne_mojet_byit_menshe_datyi_dtp']);
            invalid_elements.push(form.findField('EvnDtpWound_HospDate'));
        }
        if (set_date && set_date < dtp_date) {
            error_msg.push(lang['data_sostavleniya_izvescheniya_ne_mojet_byit_menshe_datyi_dtp']);
            invalid_elements.push(form.findField('EvnDtpWound_setDate'));
        }
        if (other_lpu_date && other_lpu_date < dtp_date) {
            error_msg.push(lang['data_perevoda_v_drugoe_lpu_ne_mojet_byit_menshe_datyi_dtp']);
            invalid_elements.push(form.findField('EvnDtpWound_setDate'));
        }
        if (other_lpu_date && other_lpu_date < obr_date) {
            error_msg.push(lang['data_perevoda_v_drugoe_lpu_ne_mojet_byit_menshe_datyi_obrascheniya_v_lpu']);
            invalid_elements.push(form.findField('EvnDtpWound_setDate'));
        }
        if (set_date && set_date < obr_date) {
            error_msg.push(lang['data_sostavleniya_izvescheniya_ne_mojet_byit_menshe_datyi_obrascheniya_v_lpu']);
            invalid_elements.push(form.findField('EvnDtpWound_setDate'));
        }
		
        if (error_msg.length > 0) {
            this.error_msg = error_msg.join("<br /><br />");
            this.focus_element = invalid_elements[0];
            return false;
        }
		
        return true;
    },

    /**
	 * Печать извещения
	 */
    doPrint: function(){
        if ( 'add' == this.action || 'edit' == this.action ) {
            this.doSave({
                print: true
            });
        }
        else if ( 'view' == this.action ) {
            var evn_dtp_wound_id = this.frameForm.getForm().findField('EvnDtpWound_id').getValue();
            window.open('/?c=EvnDtp&m=printEvnDtpWound&EvnDtpWound_id=' + evn_dtp_wound_id, '_blank');
        }
    },

    /**
	 * Сохранение извещения
	 */
    doSave: function(options) {
        if (this.formStatus == 'save') {
            return false;
        }
		
        var form = this.frameForm.getForm();
		
        this.formStatus = 'save';
		
        if (!this.validateForm()) {
            sw.swMsg.show({
                buttons: Ext.Msg.OK,
                fn: function() {
                    this.formStatus = 'edit';
                    this.focus_element.focus(false);
                },
                scope: this,
                icon: Ext.Msg.WARNING,
                msg: this.error_msg,
                title: ERR_INVFIELDS_TIT
            });
            return false;
        }
		
        var params = new Object();
		
        var loadMask = new Ext.LoadMask(this.getEl(), {
            msg: "Подождите, идет сохранение извещения о раненом в ДТП..."
        });
        loadMask.show();

        form.submit({
            failure: function(result_form, action) {
                this.formStatus = 'edit';
                loadMask.hide();
				
                if (action.result) {
                    if (action.result.Error_Msg) {
                        sw.swMsg.alert(lang['oshibka'], action.result.Error_Msg);
                    } else {
                        sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_1]']);
                    }
                }
            },
            params: params,
            scope: this,
            success: function(result_form, action) {
                this.formStatus = 'edit';
                loadMask.hide();
				
                if (action.result) {
                    if (action.result.EvnDtpWound_id) {
                        var evn_dtp_wound_id = action.result.EvnDtpWound_id;
						
                        form.findField('EvnDtpWound_id').setValue(evn_dtp_wound_id);
						
                        if (options && options.print == true) {
                            window.open('/?c=EvnDtp&m=printEvnDtpWound&EvnDtpWound_id=' + evn_dtp_wound_id, '_blank');
							
                            this.action = 'edit';
                            this.setTitle(lang['izveschenie_o_ranenom_v_dtp']);
                        } else {
                            this.hide();
                        }
                    } else {
                        if (action.result.Error_Msg) {
                            sw.swMsg.alert(lang['oshibka'], action.result.Error_Msg);
                        } else {
                            sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_3]']);
                        }
                    }
                } else {
                    sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_2]']);
                }
            }
        });
        return true;
    },
	
    /**
	 * Инициализация компонента
	 */
    initComponent: function() {
        var wnd = this;

        // Кнопки внизу формы
        this.buttonsOnForm = [{
            text: BTN_FRMSAVE,
            iconCls: 'save16',
            tabIndex: TABINDEX_EDWEW + 11,
            handler: function() {
                wnd.doSave();
            },
            onShiftTabAction: function () {
                wnd.frameForm.getForm().findField('EvnDtpWound_setDate').focus(false);
            },
            onTabAction: function() {
                wnd.buttons[1].focus();
            }

        }, {
            iconCls: 'print16',
            text: BTN_FRMPRINT,
            tabIndex: TABINDEX_EDWEW + 12,
            handler: function() {
                wnd.doPrint();
            },
            onShiftTabAction: function () {
                wnd.buttons[0].focus();
            },
            onTabAction: function() {
                wnd.buttons[wnd.buttons.length - 1].focus();
            }
        }, '-', HelpButton(this, -1), {
            iconCls: 'cancel16',
            text: BTN_FRMCANCEL,
            tabIndex: TABINDEX_EDWEW + 13,
            handler: function() {
                wnd.onCancelAction()
            },
            onTabAction: function() {
                wnd.frameForm.getForm().findField('EvnDtpWound_ObrDate').focus(false);
            },
            onShiftTabAction: function () {
                wnd.buttons[1].focus();
            }
        }];

        // Панель информации о человеке
        this.framePersonInfo = new sw.Promed.PersonInformationPanel({
            region: 'north',
            button1OnHide: function() {
                if (wnd.action == 'view') {
                    wnd.buttons[wnd.buttons.length - 1].focus();
                } else {
                    wnd.frameForm.getForm().findField('EvnDtpWound_ObrDate').focus(false);
                }
            },
            button2Callback: function(callback_data) {
                var form = wnd.frameForm;
				
                form.getForm().findField('PersonEvn_id').setValue(callback_data.PersonEvn_id);
                form.getForm().findField('Server_id').setValue(callback_data.Server_id);
				
                wnd.framePersonInfo.load({
                    Person_id: callback_data.Person_id,
                    Server_id: callback_data.Server_id
                });
            },
            button2OnHide: function() {
                wnd.framePersonInfo.button1OnHide();
            },
            button3OnHide: function() {
                wnd.framePersonInfo.button1OnHide();
            },
            button4OnHide: function() {
                wnd.framePersonInfo.button1OnHide();
            },
            button5OnHide: function() {
                wnd.framePersonInfo.button1OnHide();
            }
        });

        // Панель с формой
        this.frameForm = new Ext.form.FormPanel({
            border: true,
            frame: true,
            labelAlign: 'right',
            labelWidth: 180,
            region: 'center',
            url: '/?c=EvnDtp&m=saveEvnDtpWound',
            autoHeight: true,
            bodyStyle: 'padding-top: 0.5em;',
            layout: 'form',
            items: [{
                name: 'EvnDtpWound_id',
                value: 0,
                xtype: 'hidden'
            }, {
                name: 'Person_id',
                value: 0,
                xtype: 'hidden'
            }, {
                name: 'Lpu_id',
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
            }, {
                allowBlank: false,
                disabledClass: 'field-disabled',
                fieldLabel: lang['data_obrascheniya'],
                name: 'EvnDtpWound_ObrDate',
                tabIndex: TABINDEX_EDWEW + 1,
                width: 150,
                xtype: 'swdatefield',
                listeners: {
                    'blur': function(field, newValue, oldValue) {
                        var form = wnd.frameForm.getForm();

                        form.findField('EvnDtpWound_HospDate').setMinValue(newValue);
                        form.findField('EvnDtpWound_HospDate').validate()
                        form.findField('EvnDtpWound_OtherLpuDate').setMinValue(newValue);
                        form.findField('EvnDtpWound_OtherLpuDate').validate();
                        form.findField('EvnDtpWound_setDate').setMinValue(newValue);
                        form.findField('EvnDtpWound_setDate').validate();
                    }
                }
            }, {
                fieldLabel: lang['data_gospitalizatsii'],
                disabledClass: 'field-disabled',
                name: 'EvnDtpWound_HospDate',
                tabIndex: TABINDEX_EDWEW + 2,
                width: 150,
                xtype: 'swdatefield'
            }, {
                allowBlank: false,
                disabledClass: 'field-disabled',
                fieldLabel: lang['data_dtp'],
                name: 'EvnDtpWound_DtpDate',
                tabIndex: TABINDEX_EDWEW + 3,
                width: 150,
                xtype: 'swdatefield',
                listeners: {
                    'blur': function(field, newValue, oldValue) {
                        var form = wnd.frameForm.getForm();

                        form.findField('EvnDtpWound_ObrDate').setMinValue(newValue);
                        form.findField('EvnDtpWound_ObrDate').validate()
                        form.findField('EvnDtpWound_HospDate').setMinValue(newValue);
                        form.findField('EvnDtpWound_HospDate').validate()
                        form.findField('EvnDtpWound_OtherLpuDate').setMinValue(newValue);
                        form.findField('EvnDtpWound_OtherLpuDate').validate()
                        form.findField('EvnDtpWound_setDate').setMinValue(newValue);
                        form.findField('EvnDtpWound_setDate').validate()
                    },	
					'change': function(field, newValue, oldValue) {
						blockedDateAfterPersonDeath('personpanel', wnd.framePersonInfo, field, newValue, oldValue);
					}
                }

            }, {
                xtype: 'fieldset',
                title: lang['obraschenie_v_meditsinskuyu_organizatsiyu'],
                autoHeight: true,
                items: [{
                    allowBlank: false,
                    disabledClass: 'field-disabled',
                    fieldLabel: lang['diagnoz_pri_obraschenii'],
                    hiddenName: 'Diag_pid',
                    listWidth: 600,
                    tabIndex: TABINDEX_EDWEW + 4,
                    validateOnBlur: true,
                    width: 500,
                    xtype: 'swdiagcombo'
                }, {
                    allowBlank: false,
                    disabledClass: 'field-disabled',
                    fieldLabel: lang['vneshnaya_prichina_dtp'],
                    hiddenName: 'Diag_eid',
                    listWidth: 600,
                    tabIndex: TABINDEX_EDWEW + 5,
                    validateOnBlur: true,
                    width: 500,
                    xtype: 'swdiagcombo'
                }]
            }, {
                xtype: 'fieldset',
                title: lang['perevod_v_druguyu_organizatsiyu'],
                autoHeight: true,
                items: [{
                    fieldLabel: lang['data_perevoda_v_drugoe_lpu'],
                    disabledClass: 'field-disabled',
                    name: 'EvnDtpWound_OtherLpuDate',
                    tabIndex: TABINDEX_EDWEW + 6,
                    width: 150,
                    xtype: 'swdatefield'
                }, {
                    allowBlank: true,
                    disabledClass: 'field-disabled',
                    fieldLabel: lang['lpu_kuda_pereveden_ranenyiy'],
                    editable: true,
                    forceSelection: false,
                    hiddenName: 'Lpu_oid',
                    width: 450,
                    listWidth: 450,
                    typeAhead: true,
                    xtype: 'swlpulocalcombo',
                    tabIndex: TABINDEX_EDWEW + 7
                }, {
                    fieldLabel: lang['diagnoz_pri_perevode'],
                    disabledClass: 'field-disabled',
                    hiddenName: 'Diag_oid',
                    listWidth: 600,
                    tabIndex: TABINDEX_EDWEW + 8,
                    validateOnBlur: true,
                    width: 500,
                    xtype: 'swdiagcombo'
                }]
            }, {
                xtype: 'fieldset',
                title: lang['izveschenie'],
                autoHeight: true,
                items: [{
                    allowBlank: false,
                    disabledClass: 'field-disabled',
                    hiddenName: 'MedPersonal_id',
                    width: 550,
                    listWidth: 650,
                    lastQuery: '',
					anchor:'',
					xtype: 'swmedpersonalcombo',//xtype: 'swmedstafffactglobalcombo',
                    fieldLabel: lang['vrach_sostavivshiy_izveschenie'],
                    tabIndex: TABINDEX_EDWEW + 9
                }, {
                    allowBlank: false,
                    disabledClass: 'field-disabled',
                    fieldLabel: lang['data_sostavleniya_izvescheniya'],
                    name: 'EvnDtpWound_setDate',
                    tabIndex: TABINDEX_EDWEW + 10,
                    width: 150,
                    xtype: 'swdatefield'
                }]
            }]
        });
		
        Ext.apply(this, {
            buttons: this.buttonsOnForm,
            items: [this.framePersonInfo, this.frameForm]
        });
        sw.Promed.swEvnDtpWoundEditWindow.superclass.initComponent.apply(this, arguments);
    },
	
    /**
	 * Установка режима формы (view/edit)
	 */
    setFormMode: function(form_mode) {
        var form = this.frameForm.getForm();
        var xtypes = ['swdatefield', 'swmedpersonalcombo', 'swdiagcombo', 'swlpulocalcombo']; //swmedstafffactglobalcombo
		
        form.items.each(function(f) {
            if (f.getXType().inlist(xtypes)) {
                if (form_mode == 'view') {
                    f.disable();
                } else if (form_mode == 'edit' || form_mode == 'add') {
                    f.enable();
                }
            }
        });
        if (form_mode == 'view') {
            this.buttons[0].hide();
        } else if (form_mode == 'edit' || form_mode == 'add') {
            this.buttons[0].show();
        }
    },

    /**
	 * Установка значений полей в форме после загрузки
	 */
    setFormValues: function() {
        var form = this.frameForm.getForm();
		
        var diag_p_combo = form.findField('Diag_pid');
        var diag_e_combo = form.findField('Diag_eid');
        var diag_o_combo = form.findField('Diag_oid');
		
        var diag_pid = diag_p_combo.getValue();
        var diag_eid = diag_e_combo.getValue();
        var diag_oid = diag_o_combo.getValue();
        var record;
		
        if (diag_pid) {
            diag_p_combo.getStore().load({
                callback: function() {
                    diag_p_combo.getStore().each(function(record) {
                        if (record.get('Diag_id') == diag_pid) {
                            diag_p_combo.fireEvent('select', diag_p_combo, record, 0);
                        }
                    });
                },
                params: {
                    where: "where DiagLevel_id = 4 and Diag_id = " + diag_pid
                }
            });
        }
		
        if (diag_eid) {
            diag_e_combo.getStore().load({
                callback: function() {
                    diag_e_combo.getStore().each(function(record) {
                        if (record.get('Diag_id') == diag_eid) {
                            diag_e_combo.fireEvent('select', diag_e_combo, record, 0);
                        }
                    });
                },
                params: {
                    where: "where DiagLevel_id = 4 and Diag_id = " + diag_eid
                }
            });
        }
		
        if (diag_oid) {
            diag_o_combo.getStore().load({
                callback: function() {
                    diag_o_combo.getStore().each(function(record) {
                        if (record.get('Diag_id') == diag_oid) {
                            diag_p_combo.fireEvent('select', diag_o_combo, record, 0);
                        }
                    });
                },
                params: {
                    where: "where DiagLevel_id = 4 and Diag_id = " + diag_oid
                }
            });
        }
    },
	
    /**
	 * Show окна просмотра/редактирования
	 */
    show: function() {
        sw.Promed.swEvnDtpWoundEditWindow.superclass.show.apply(this, arguments);
		
        if (!arguments[0]) {
            sw.swMsg.alert(lang['soobschenie'], lang['nevernyie_parametryi']);
            return false;
        }
		
        var wnd = this;
        var form = wnd.frameForm.getForm();
		
        form.reset();
		
        form.setValues(arguments[0]);
        form.findField('Lpu_id').setValue(Ext.globalOptions.globals.lpu_id);
		
        var loadMask = new Ext.LoadMask(this.getEl(), {
            msg: LOAD_WAIT
        });
        loadMask.show();

        var person_id = form.findField('Person_id').getValue();
        var server_id = form.findField('Server_id').getValue();
		
        this.framePersonInfo.load({
            callback: function() {
                var person_birthday = wnd.framePersonInfo.getFieldValue('Person_Birthday');
                form.items.each(function(f) {
                    if (f.getXType()=='swdatefield') {
                        f.setMinValue(person_birthday);
                    }
                });
            },
            Person_id: person_id,
            Server_id: server_id
        });

        form.items.each(function(f) {
            if (f.getXType()=='swdatefield') {
                setCurrentDateTime({
                    dateField: f,
                    loadMask: false,
                    setDate: false,
                    setDateMaxValue: true,
                    setDateMinValue: false,
                    setTime: false,
                    windowId: wnd.id
                });
            }
        });

        if (arguments[0].action) {
            wnd.action = arguments[0].action;
        }

        if ( arguments[0].callback ) {
            wnd.callback = arguments[0].callback;
        }
		
        setMedStaffFactGlobalStoreFilter();
        form.findField('MedPersonal_id').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));

        var evn_dtp_wound_id = form.findField('EvnDtpWound_id').getValue();

        this.setFormMode(this.action);
        switch (this.action) {
            case 'add':
                this.setTitle(lang['izveschenie_o_ranenom_v_dtp_dobavlenie']);

                loadMask.hide();
				
                break;
				
            case 'view':
            case 'edit':

                var title = (this.action == 'edit')
                    ? lang['izveschenie_o_ranenom_v_dtp_redaktirovanie']
                    : lang['izveschenie_o_ranenom_v_dtp_prosmotr'];

                this.setTitle(title);
				
                form.load({
                    scope: this,
                    params: {
                        EvnDtpWound_id: evn_dtp_wound_id
                    },
                    failure: function() {
                        sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_zagruzke_dannyih_formyi'], function() {
                            wnd.hide();
                        });
                    },
                    success: function() {
                        this.setFormValues();

                        loadMask.hide();
                    },
                    url: '/?c=EvnDtp&m=loadEvnDtpWoundEditForm'
                });
                break;
				
        }

        form.findField('EvnDtpWound_ObrDate').focus(false, 300);
        form.clearInvalid();

        return true;
    }
});
