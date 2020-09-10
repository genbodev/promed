/**
 * swEvnDtpDeathEditWindow - окно редактирования/добавления/просмотра извещения о скончавшемся в ДТП.
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/projects/promed
 *
 * @package      Hospital
 * @access       public
 * @copyright    Copyright (c) 2016 Swan Ltd.
 * @author       Alexander Kurakin (a.kurakin@swan.perm.ru)
 * @version      05.02.2016
 * @comment      Префикс для id компонентов EDDEW (swEvnDtpDeathEditWindow)
 *
 */
sw.Promed.swEvnDtpDeathEditWindow = Ext.extend(sw.Promed.BaseForm, {
    action: null,
    buttonAlign: 'left',
    callback: Ext.emptyFn,
    closeAction: 'hide',
    formStatus: 'edit',
    layout: 'border',
    maximized: true,
    iconCls: 'stac-accident-dead16',
    title: 'Извещение о скончавшемся в ДТП',
    id: 'EvnDtpDeathEditWindow',

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
        var set_date = form.findField('EvnDtpDeath_setDate').getValue();
        var hosp_date = form.findField('EvnDtpDeath_HospDate').getValue();
        var dtp_date = form.findField('EvnDtpDeath_DtpDate').getValue();
        var person_birthday = this.framePersonInfo.getFieldValue('Person_Birthday');
        var death_place = this.findById('EDDEW_DtpDeathPlace').getValue();
		
        if (death_place && death_place != 1 && !this.findById('EDDEW_DtpDeathTime').getValue()) {
            error_msg.push('Поле В течение обязательно для заполнения!');
            invalid_elements.push(this.findById('EDDEW_DtpDeathTime'));
        }
        if (hosp_date && !form.findField('Diag_pid').getValue()) {
            error_msg.push('Поле диагноз при поступлении обязательно для заполнения!');
            invalid_elements.push(form.findField('Diag_pid'));
        }
        if (dtp_date > cur_date) {
            error_msg.push('Дата ДТП не может быть больше текущей даты!');
            invalid_elements.push(form.findField('EvnDtpDeath_DtpDate'));
        }
        if (hosp_date && hosp_date > cur_date) {
            error_msg.push('Дата поступления в стационар не может быть больше текущей даты!');
            invalid_elements.push(form.findField('EvnDtpDeath_HospDate'));
        }
        if (set_date && set_date > cur_date) {
            error_msg.push('Дата составления извещения не может быть больше текущей даты!');
            invalid_elements.push(form.findField('EvnDtpDeath_setDate'));
        }
        if (person_birthday) {
            if (dtp_date < person_birthday) {
                error_msg.push('Дата ДТП не может быть меньше даты рождения!');
                invalid_elements.push(form.findField('EvnDtpDeath_DtpDate'));
            }
            if (hosp_date && hosp_date < person_birthday) {
                error_msg.push('Дата поступления в стационар не может быть меньше даты рождения!');
                invalid_elements.push(form.findField('EvnDtpDeath_HospDate'));
            }
            if (set_date && set_date < person_birthday) {
                error_msg.push('Дата составления извещения не может быть меньше даты рождения!');
                invalid_elements.push(form.findField('EvnDtpDeath_setDate'));
            }
        }
        if (hosp_date && hosp_date < dtp_date) {
            error_msg.push('Дата поступления в стационар не может быть меньше даты ДТП!');
            invalid_elements.push(form.findField('EvnDtpDeath_HospDate'));
        }
        if (set_date && set_date < dtp_date) {
            error_msg.push('Дата составления извещения не может быть меньше даты ДТП!');
            invalid_elements.push(form.findField('EvnDtpDeath_setDate'));
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
            var evn_dtp_death_id = this.frameForm.getForm().findField('EvnDtpDeath_id').getValue();
            window.open('/?c=EvnDtpDeath&m=printEvnDtpDeath&EvnDtpDeath_id=' + evn_dtp_death_id, '_blank');
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
            msg: "Подождите, идет сохранение извещения о скончавшемся в ДТП..."
        });
        loadMask.show();

        form.submit({
            failure: function(result_form, action) {
                this.formStatus = 'edit';
                loadMask.hide();
				
                if (action.result) {
                    if (action.result.Error_Msg) {
                        sw.swMsg.alert('Ошибка', action.result.Error_Msg);
                    } else {
                        sw.swMsg.alert('Ошибка', 'При сохранении произошли ошибки [Тип ошибки: 1]');
                    }
                }
            },
            params: params,
            scope: this,
            success: function(result_form, action) {
                this.formStatus = 'edit';
                loadMask.hide();
				
                if (action.result) {
                    if (action.result.EvnDtpDeath_id) {
                        var evn_dtp_death_id = action.result.EvnDtpDeath_id;
						
                        form.findField('EvnDtpDeath_id').setValue(evn_dtp_death_id);
						
                        if (options && options.print == true) {
                            window.open('/?c=EvnDtpDeath&m=printEvnDtpDeath&EvnDtpDeath_id=' + evn_dtp_death_id, '_blank');
							
                            this.action = 'edit';
                            this.setTitle('Извещение о скончавшемся в ДТП');
                        } else {
                            this.hide();
                        }
                    } else {
                        if (action.result.Error_Msg) {
                            sw.swMsg.alert('Ошибка', action.result.Error_Msg);
                        } else {
                            sw.swMsg.alert('Ошибка', 'При сохранении произошли ошибки [Тип ошибки: 3]');
                        }
                    }
                } else {
                    sw.swMsg.alert('Ошибка', 'При сохранении произошли ошибки [Тип ошибки: 2]');
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
            tabIndex: TABINDEX_EDDEW + 12,
            handler: function() {
                wnd.doSave();
            },
            onShiftTabAction: function () {
                wnd.frameForm.getForm().findField('MedStaffFact_id').focus(false);
            },
            onTabAction: function() {
                wnd.buttons[1].focus();
            }

        }, {
            iconCls: 'print16',
            text: BTN_FRMPRINT,
            tabIndex: TABINDEX_EDDEW + 13,
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
            tabIndex: TABINDEX_EDDEW + 14,
            handler: function() {
                wnd.onCancelAction()
            },
            onTabAction: function() {
                wnd.frameForm.getForm().findField('EvnDtpDeath_DtpDate').focus(false);
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
                    wnd.frameForm.getForm().findField('EvnDtpDeath_DtpDate').focus(false);
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
            labelWidth: 250,
            region: 'center',
            url: '/?c=EvnDtpDeath&m=saveEvnDtpDeath',
            autoHeight: true,
            bodyStyle: 'padding-top: 0.5em;',
            layout: 'form',
            items: [{
                name: 'EvnDtpDeath_id',
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
                fieldLabel: 'Дата ДТП',
                name: 'EvnDtpDeath_DtpDate',
                tabIndex: TABINDEX_EDDEW + 1,
                width: 150,
                xtype: 'swdatefield',
                listeners: {
                    'blur': function(field, newValue, oldValue) {
                        var form = wnd.frameForm.getForm();
                        form.findField('EvnDtpDeath_HospDate').setMinValue(newValue);
                        form.findField('EvnDtpDeath_HospDate').validate();
                        form.findField('EvnDtpDeath_setDate').setMinValue(newValue);
                        form.findField('EvnDtpDeath_setDate').validate();
                    },	
					'change': function(field, newValue, oldValue) {
						blockedDateAfterPersonDeath('personpanel', wnd.framePersonInfo, field, newValue, oldValue);
					}
                }

            }, {
                fieldLabel: 'Дата поступления в стационар',
                disabledClass: 'field-disabled',
                name: 'EvnDtpDeath_HospDate',
                tabIndex: TABINDEX_EDDEW + 2,
                width: 150,
                allowBlank: true,
                xtype: 'swdatefield',
                listeners: {
                    'blur': function(field, newValue, oldValue) {
                        var form = wnd.frameForm.getForm();
                        form.findField('EvnDtpDeath_DtpDate').setMinValue(newValue);
                        form.findField('EvnDtpDeath_DtpDate').validate();
                        form.findField('EvnDtpDeath_setDate').setMinValue(newValue);
                        form.findField('EvnDtpDeath_setDate').validate();
                    },  
                    'change': function(field, newValue, oldValue) {
                        var form = wnd.frameForm.getForm();
                        if(!Ext.isEmpty(newValue)){
                            form.findField('Diag_pid').enable();
                        } else {
                            form.findField('Diag_pid').setValue('');
                            form.findField('Diag_pid').disable();
                        }
                    }
                }
            }, {
                allowBlank: true,
                disabled: true,
                disabledClass: 'field-disabled',
                fieldLabel: 'Диагноз при поступлении',
                hiddenName: 'Diag_pid',
                listWidth: 600,
                tabIndex: TABINDEX_EDDEW + 3,
                width: 500,
                xtype: 'swdiagcombo'
            }, {
                allowBlank: false,
                disabledClass: 'field-disabled',
                fieldLabel: 'Дата смерти',
                name: 'EvnDtpDeath_DeathDate',
                tabIndex: TABINDEX_EDDEW + 4,
                width: 150,
                xtype: 'swdatefield',
                listeners: {
                    'change': function(field, newValue, oldValue) {
                        blockedDateAfterPersonDeath('personpanel', wnd.framePersonInfo, field, newValue, oldValue);
                    }
                }

            }, {
                xtype: 'fieldset',
                title: 'Причина смерти',
                autoHeight: true,
                labelWidth: 240,
                items: [{
                    allowBlank: false,
                    disabledClass: 'field-disabled',
                    fieldLabel: 'Непосредственная причина смерти',
                    hiddenName: 'Diag_iid',
                    listWidth: 600,
                    tabIndex: TABINDEX_EDDEW + 5,
                    validateOnBlur: true,
                    width: 500,
                    xtype: 'swdiagcombo'
                }, {
                    allowBlank: false,
                    disabledClass: 'field-disabled',
                    fieldLabel: 'Основная причина смерти',
                    hiddenName: 'Diag_mid',
                    listWidth: 600,
                    tabIndex: TABINDEX_EDDEW + 6,
                    validateOnBlur: true,
                    width: 500,
                    xtype: 'swdiagcombo'
                }, {
                    allowBlank: false,
                    disabledClass: 'field-disabled',
                    fieldLabel: 'Внешняя причина смерти',
                    hiddenName: 'Diag_eid',
                    listWidth: 600,
                    tabIndex: TABINDEX_EDDEW + 7,
                    validateOnBlur: true,
                    width: 500,
                    xtype: 'swdiagcombo'
                }]
            }, {
                allowBlank: false,
                fieldLabel: 'Смерть наступила',
                comboSubject: 'DtpDeathPlace',
                id: 'EDDEW_DtpDeathPlace',
                tabIndex: TABINDEX_EDDEW + 8,
                width: 250,
                xtype: 'swcommonsprcombo',
                listeners: {
                    'blur': function(field, newValue, oldValue) {
                        var form = wnd.frameForm.getForm();
                    },  
                    'change': function(field, newValue, oldValue) {
                        var form = wnd.frameForm.getForm();
                        if((newValue == 2) || (newValue == 3)){
                            wnd.findById('EDDEW_DtpDeathTime').enable();
                            var dtpDate = form.findField('EvnDtpDeath_DtpDate');
                            var deathDate = form.findField('EvnDtpDeath_DeathDate');
                            if(!Ext.isEmpty(dtpDate.getValue())&&!Ext.isEmpty(deathDate.getValue())){
                                var result = (deathDate.getValue() - dtpDate.getValue())/(1000*60*60*24);
                                if(result<=7){
                                    wnd.findById('EDDEW_DtpDeathTime').setValue(1);
                                } else {
                                    wnd.findById('EDDEW_DtpDeathTime').setValue(2);
                                }
                            }
                        } else {
                            wnd.findById('EDDEW_DtpDeathTime').setValue(null);
                            wnd.findById('EDDEW_DtpDeathTime').disable();
                        }
                    }
                }
            }, {
                allowBlank: true,
                disabled: true,
                fieldLabel: 'В течение',
                comboSubject: 'DtpDeathTime',
                id: 'EDDEW_DtpDeathTime',
                tabIndex: TABINDEX_EDDEW + 9,
                width: 250,
                xtype: 'swcommonsprcombo'
            }, {
                xtype: 'fieldset',
                title: 'Извещение',
                autoHeight: true,
                labelWidth: 240,
                items: [{
                    allowBlank: false,
                    disabledClass: 'field-disabled',
                    fieldLabel: 'Дата заполнения извещения',
                    name: 'EvnDtpDeath_setDate',
                    tabIndex: TABINDEX_EDDEW + 10,
                    width: 150,
                    xtype: 'swdatefield'
                }, {
                    allowBlank: false,
                    disabledClass: 'field-disabled',
                    hiddenName: 'MedStaffFact_id',
                    width: 500,
                    listWidth: 650,
                    lastQuery: '',
					anchor:'',
					xtype: 'swmedstafffactglobalcombo',//'swmedpersonalcombo'
                    fieldLabel: 'Врач, заполнивший извещение',
                    tabIndex: TABINDEX_EDDEW + 11
                }]
            }]
        });
		
        Ext.apply(this, {
            buttons: this.buttonsOnForm,
            items: [this.framePersonInfo, this.frameForm]
        });
        sw.Promed.swEvnDtpDeathEditWindow.superclass.initComponent.apply(this, arguments);
    },
	
    /**
	 * Установка режима формы (view/edit)
	 */
    setFormMode: function(form_mode) {
        var form = this.frameForm.getForm();
        var xtypes = ['swdatefield', 'swmedstafffactglobalcombo', 'swdiagcombo', 'swcommonsprcombo']; // swmedpersonalcombo
		
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
        var diag_i_combo = form.findField('Diag_iid');
        var diag_m_combo = form.findField('Diag_mid');
        var death_place_combo = this.findById('EDDEW_DtpDeathPlace');
        var hosp_combo = form.findField('EvnDtpDeath_HospDate');
		
        var diag_pid = diag_p_combo.getValue();
        var diag_eid = diag_e_combo.getValue();
        var diag_iid = diag_i_combo.getValue();
        var diag_mid = diag_m_combo.getValue();
        var death_place = death_place_combo.getValue();
        var hosp = hosp_combo.getValue();
        var record;
		
        if (hosp){
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
                if(this.action == 'edit'){
                    diag_p_combo.enable();
                }
            }
        } else {
            diag_p_combo.setValue('');
            diag_p_combo.disable();
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
		
        if (diag_iid) {
            diag_i_combo.getStore().load({
                callback: function() {
                    diag_i_combo.getStore().each(function(record) {
                        if (record.get('Diag_id') == diag_iid) {
                            diag_i_combo.fireEvent('select', diag_i_combo, record, 0);
                        }
                    });
                },
                params: {
                    where: "where DiagLevel_id = 4 and Diag_id = " + diag_iid
                }
            });
        }

        if (diag_mid) {
            diag_m_combo.getStore().load({
                callback: function() {
                    diag_m_combo.getStore().each(function(record) {
                        if (record.get('Diag_id') == diag_mid) {
                            diag_m_combo.fireEvent('select', diag_m_combo, record, 0);
                        }
                    });
                },
                params: {
                    where: "where DiagLevel_id = 4 and Diag_id = " + diag_mid
                }
            });
        }

        if(death_place && death_place != 1 && this.action == 'edit'){
            this.findById('EDDEW_DtpDeathTime').enable();
        } else {
            this.findById('EDDEW_DtpDeathTime').disable();
        }
    },
	
    /**
	 * Show окна просмотра/редактирования
	 */
    show: function() {
        sw.Promed.swEvnDtpDeathEditWindow.superclass.show.apply(this, arguments);
		
        if (!arguments[0]) {
            sw.swMsg.alert('Сообщение', 'Неверные параметры');
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
        form.findField('MedStaffFact_id').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));

        var evn_dtp_death_id = form.findField('EvnDtpDeath_id').getValue();

        this.setFormMode(this.action);
        switch (this.action) {
            case 'add':
                this.setTitle('Извещение о скончавшемся в ДТП: Добавление');
                var cur_date = new Date();
                form.findField('EvnDtpDeath_setDate').setValue(cur_date);
                form.findField('MedStaffFact_id').setValue(getGlobalOptions().CurMedStaffFact_id);
                loadMask.hide();
				
                break;
				
            case 'view':
            case 'edit':

                var title = (this.action == 'edit')
                    ? lang['izveschenie_o_skonchavshemsya_v_dtp_redaktirovanie']
                    : lang['izveschenie_o_skonchavshemsya_v_dtp_prosmotr'];

                this.setTitle(title);

                form.load({
                    scope: this,
                    params: {
                        EvnDtpDeath_id: evn_dtp_death_id
                    },
                    failure: function() {
                        sw.swMsg.alert('Ошибка', 'Ошибка при загрузке данных формы', function() {
                            wnd.hide();
                        });
                    },
                    success: function() {
                        this.setFormValues();

                        loadMask.hide();
                    },
                    url: '/?c=EvnDtpDeath&m=loadEvnDtpDeathEditForm'
                });
                break;
				
        }

        form.findField('EvnDtpDeath_DtpDate').focus(false, 300);
        form.clearInvalid();

        return true;
    }
});
