/**
* swMorbusOnkoBasePSWindow - окно редактирования "Госпитализация"
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

Ext6.define('common.MorbusOnko.swMorbusOnkoBasePSWindow', {
	/* свойства */
	requires: [
		'common.EMK.PersonInfoPanelShort',
	],
	alias: 'widget.swMorbusOnkoBasePSWindow',
    autoShow: false,
	closable: true,
	cls: 'arm-window-new emkd',
	constrain: true,
	extend: 'base.BaseForm',
	findWindow: false,
    header: true,
	modal: true,
	layout: 'form',
	refId: 'MorbusOnkoBasePSeditsw',
	renderTo: Ext.getCmp('main-center-panel').body.dom,
	resizable: false,
    winTitle: langs('Госпитализация'),
	title: langs('Госпитализация'),
	width: 800,
	height: 650,
	
	save:  function() {
		var win = this;
		if ( !this.form.isValid() )
		{
			sw.swMsg.show(
			{
				buttons: Ext.Msg.OK,
				fn: function() 
				{
					win.findById('MorbusOnkoBasePSEditForm').getFirstInvalidEl().focus(true);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		win.mask(LOAD_WAIT_SAVE);
		var formParams = this.form.getValues();
        formParams.MorbusOnkoBasePS_IsTreatDelay = (formParams.MorbusOnkoBasePS_IsTreatDelay)?2:1;
        formParams.MorbusOnkoBasePS_IsNotTreat = (formParams.MorbusOnkoBasePS_IsNotTreat)?2:1;
        formParams.MorbusOnkoBasePS_IsSurg = (formParams.MorbusOnkoBasePS_IsSurg)?2:1;
        formParams.MorbusOnkoBasePS_IsPreOper = (formParams.MorbusOnkoBasePS_IsPreOper)?2:1;
        formParams.MorbusOnkoBasePS_IsIntraOper = (formParams.MorbusOnkoBasePS_IsIntraOper)?2:1;
        formParams.MorbusOnkoBasePS_IsPostOper = (formParams.MorbusOnkoBasePS_IsPostOper)?2:1;
        formParams.MorbusOnkoBasePS_IsBeam = (formParams.MorbusOnkoBasePS_IsBeam)?2:1;
        formParams.MorbusOnkoBasePS_IsChem = (formParams.MorbusOnkoBasePS_IsChem)?2:1;
        formParams.MorbusOnkoBasePS_IsGormun = (formParams.MorbusOnkoBasePS_IsGormun)?2:1;
        formParams.MorbusOnkoBasePS_IsImmun = (formParams.MorbusOnkoBasePS_IsImmun)?2:1;
        formParams.MorbusOnkoBasePS_IsOther = (formParams.MorbusOnkoBasePS_IsOther)?2:1;
        Ext.Ajax.request({
            failure:function () {
                win.unmask();
            },
            params: formParams,
            method: 'POST',
            success: function (result) {
                win.unmask();
                if (result.responseText) {
                    var response = Ext.util.JSON.decode(result.responseText);
                    formParams.MorbusOnkoBasePS_id = response.MorbusOnkoBasePS_id;
                    win.callback(formParams);
                    win.hide();
                }
            },
            url:'/?c=MorbusOnkoBasePS&m=save'
        });
	},
	
    setFieldsDisabled: function(d) {
        var form = this;
        this.FormPanel.items.each(function(f){
            if (f && (f.xtype!='hidden') && (f.xtype!='fieldset')  && (f.changeDisabled!==false)) {
                f.setDisabled(d);
            }
        });
       // form.MorbusOnkoTumorStatusFrame.setReadOnly(d);
        //form.buttons[0].setDisabled(d);
    },
    onLoadForm: function(formParams) {
        var accessType = formParams.accessType || 'edit';
        this.setFieldsDisabled(this.action == 'view' || accessType == 'view');

        formParams.MorbusOnkoBasePS_IsTreatDelay = (formParams.MorbusOnkoBasePS_IsTreatDelay && formParams.MorbusOnkoBasePS_IsTreatDelay == 2);
        formParams.MorbusOnkoBasePS_IsNotTreat = (formParams.MorbusOnkoBasePS_IsNotTreat && formParams.MorbusOnkoBasePS_IsNotTreat == 2);
        formParams.MorbusOnkoBasePS_IsSurg = (formParams.MorbusOnkoBasePS_IsSurg && formParams.MorbusOnkoBasePS_IsSurg == 2);
        formParams.MorbusOnkoBasePS_IsPreOper = (formParams.MorbusOnkoBasePS_IsPreOper && formParams.MorbusOnkoBasePS_IsPreOper == 2);
        formParams.MorbusOnkoBasePS_IsIntraOper = (formParams.MorbusOnkoBasePS_IsIntraOper && formParams.MorbusOnkoBasePS_IsIntraOper == 2);
        formParams.MorbusOnkoBasePS_IsPostOper = (formParams.MorbusOnkoBasePS_IsPostOper && formParams.MorbusOnkoBasePS_IsPostOper == 2);
        formParams.MorbusOnkoBasePS_IsBeam = (formParams.MorbusOnkoBasePS_IsBeam && formParams.MorbusOnkoBasePS_IsBeam == 2);
        formParams.MorbusOnkoBasePS_IsChem = (formParams.MorbusOnkoBasePS_IsChem && formParams.MorbusOnkoBasePS_IsChem == 2);
        formParams.MorbusOnkoBasePS_IsGormun = (formParams.MorbusOnkoBasePS_IsGormun && formParams.MorbusOnkoBasePS_IsGormun == 2);
        formParams.MorbusOnkoBasePS_IsImmun = (formParams.MorbusOnkoBasePS_IsImmun && formParams.MorbusOnkoBasePS_IsImmun == 2);
        formParams.MorbusOnkoBasePS_IsOther = (formParams.MorbusOnkoBasePS_IsOther && formParams.MorbusOnkoBasePS_IsOther == 2);
        this.form.setValues(formParams);
        var surg_chbox = this.form.findField('MorbusOnkoBasePS_IsSurg');
        surg_chbox.fireEvent('change', surg_chbox, surg_chbox.getValue());
        var lpu_combo = this.form.findField('Lpu_id');
        lpu_combo.fireEvent('change', lpu_combo, lpu_combo.getValue(), null);
    },
	onSprLoad: function(arguments) {
		
        var win = this;	
		this.action = 'add';
		this.callback = Ext.emptyFn;
        if ( !arguments[0] || !arguments[0].formParams || !arguments[0].formParams.Person_id) {
            sw.swMsg.alert(langs('Ошибка'), langs('Не указаны входные данные'), function() { win.hide(); });
            return false;
        }
        this.Person_id = arguments[0].formParams.Person_id;
		if ( arguments[0].action ) {
			this.action = arguments[0].action;
		}
		if ( arguments[0].callback && typeof arguments[0].callback == 'function' ) {
			this.callback = arguments[0].callback;
		}
		this.form.reset();
        var ls_combo = this.form.findField('LpuSection_id');
        ls_combo.getStore().clearFilter();
        ls_combo.lastQuery = '';
		
		switch (arguments[0].action) {
			case 'add':
                this.setTitle(this.winTitle +langs(': Добавление'));
                this.onLoadForm(arguments[0].formParams);
				break;
			case 'edit':
				this.setTitle(this.winTitle +langs(': Редактирование'));
				break;
			case 'view':
				this.setTitle(this.winTitle +langs(': Просмотр'));
				break;
		}
		
		win.PersonInfoPanel.load({
			Person_id: arguments[0].formParams.Person_id
		});
		
        win.mask(LOAD_WAIT);
		
		switch (this.action) {
			case 'add':
				win.unmask();
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
						MorbusOnkoBasePS_id: arguments[0].formParams.MorbusOnkoBasePS_id
					},
                    method: 'POST',
					success: function (response) {
						var result = Ext.util.JSON.decode(response.responseText);
						if (!result[0]) { return false; }

                        win.onLoadForm(result[0]);
						win.unmask();
                        return true;
					},
					url:'/?c=MorbusOnkoBasePS&m=load'
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
		
		win.PersonInfoPanel = Ext6.create('common.EMK.PersonInfoPanelShort', {
			region: 'north',
			addToolbar: false,
			bodyPadding: '3 20 0 25',
			border: false,
			height: 70,
			userMedStaffFact: this.userMedStaffFact,
			style: 'border-bottom: 1px solid #d0d0d0;',
			ownerWin: this
		});

		win.FormPanel = new Ext6.form.FormPanel({
			border: false,
			cls: 'emk_forms accordion-panel-window subFieldPanel',
			bodyPadding: '15 25 15 37',
			defaults: {
				labelAlign: 'left',
				labelWidth: 200
			},
			reader: Ext6.create('Ext6.data.reader.Json', {
				type: 'json',
				model: win.id + '_FormModel'
			}),
			url: '/?c=MorbusOnkoBasePS&m=save',
			items: [{
                name: 'MorbusOnkoBasePS_id',
                xtype: 'hidden',
                value: 0
            }, {
                name: 'MorbusOnkoBase_id',
                xtype: 'hidden',
                value: 0
            }, {
                name: 'Evn_id',
                xtype: 'hidden',
                value: 0
            }, {
                fieldLabel: langs('Дата поступления'),
                name: 'MorbusOnkoBasePS_setDT',
                xtype: 'datefield',
                allowBlank: false,
                listeners: {
                    'change':function (field, newValue) {
                        var ls_combo = win.form.findField('LpuSection_id');
                        var lpu_combo = win.form.findField('Lpu_id');
                        var dis_dt_field = win.form.findField('MorbusOnkoBasePS_disDT');

                        var ls_combo_value = ls_combo.getValue();
                        var on_date = '';

                        if (newValue) {
                            dis_dt_field.setMinValue(newValue);
                            on_date = Ext.util.Format.date(newValue, 'd.m.Y');
                        }
                        else {
                            dis_dt_field.setMinValue(null);
                        }

                        ls_combo.lastQuery = '';
                        ls_combo.clearValue();
                        ls_combo.getStore().removeAll();
                        ls_combo.getStore().load({
                            params: {
                                Lpu_id: lpu_combo.getValue(),
                                onDate: on_date,
                                isStac: '2'
                            },
                            callback: function() {
                                var index = ls_combo.getStore().findBy(function(record) {
                                    return ( record.get('LpuSection_id') == ls_combo_value );
                                }.createDelegate(this));
                                var record = ls_combo.getStore().getAt(index);
                                if ( record ) {
                                    ls_combo.setValue(ls_combo_value);
                                    ls_combo.fireEvent('change', ls_combo, ls_combo_value, null);
                                }
                                else {
                                    ls_combo.clearValue();
                                    ls_combo.fireEvent('change', ls_combo, null);
                                }
                            }
                        });
                    }
                }
            }, {
                fieldLabel: langs('Дата выписки'),
                name: 'MorbusOnkoBasePS_disDT',
                xtype: 'datefield'
            }, {
                fieldLabel: langs('Первичная/повторная'),
                name: 'OnkoHospType_id',
                xtype: 'commonSprCombo',
                sortField:'OnkoHospType_Code',
                comboSubject: 'OnkoHospType',
                width: 700
            }, {
                fieldLabel: langs('Диагноз'),
                name: 'Diag_id',
                width: 700,
                xtype: 'swDiagCombo'
            }, {
                fieldLabel: langs('Цель госпитализации'),
                name: 'OnkoPurposeHospType_id',
                xtype: 'commonSprCombo',
                allowBlank: false,
				typeCode: 'int',
                sortField:'OnkoPurposeHospType_Code',
                comboSubject: 'OnkoPurposeHospType',
                width: 700
            }, {
                fieldLabel: langs('МО проведения'),
                width: 700,
                autoLoad: true,
                name: 'Lpu_id',
                xtype: 'swLpuCombo',
                listeners: {
                    change: function(combo, newValue){
                        var ls_combo = win.form.findField('LpuSection_id');
                        var ls_combo_value = ls_combo.getValue();
                        var on_date = '';
                        var set_dt = win.form.findField('MorbusOnkoBasePS_setDT').getValue();
                        if (set_dt) {
                            on_date =Ext.util.Format.date(set_dt, 'd.m.Y')
                        }

                        ls_combo.lastQuery = '';
                        ls_combo.clearValue();
                        ls_combo.getStore().removeAll();
                        ls_combo.getStore().load({
                            params: {
                                Lpu_id: newValue,
                                onDate: on_date,
                                isStac: '2'
                            },
                            callback: function() {
                                var index = ls_combo.getStore().findBy(function(record) {
                                    return ( record.get('LpuSection_id') == ls_combo_value );
                                }.createDelegate(this));
                                var record = ls_combo.getStore().getAt(index);
                                if ( record ) {
                                    ls_combo.setValue(ls_combo_value);
                                    ls_combo.fireEvent('change', ls_combo, ls_combo_value, null);
                                }
                                else {
                                    ls_combo.clearValue();
                                    ls_combo.fireEvent('change', ls_combo, null);
                                }
                            }
                        });
                    }
                }
            }, {
                fieldLabel: langs('Отделение стационара'),
                name: 'LpuSection_id',
                lastQuery: '',
                listWidth: 650,
                width: 700,
                xtype: 'SwLpuSectionGlobalCombo'
            }, {
                title: langs('Проведено специальное лечение'),
                xtype: 'fieldset',
                anchor: '100%',
                autoHeight: true,
                style: 'padding: 10px 15px; margin: 10px 0 15px;',
                items: [{
                    boxLabel: langs('Обследование, лечение отсрочено'),
                    hideLabel: true,
                    name: 'MorbusOnkoBasePS_IsTreatDelay',
                    anchor: '100%',
                    xtype: 'checkbox',
                    listeners: {
                        change: function(checkbox,checked){
                            if (checked) {
                                win.form.findField('MorbusOnkoBasePS_IsNotTreat').setValue(false);
                                win.form.findField('MorbusOnkoBasePS_IsSurg').setValue(false);
                                win.form.findField('MorbusOnkoBasePS_IsBeam').setValue(false);
                                win.form.findField('MorbusOnkoBasePS_IsChem').setValue(false);
                                win.form.findField('MorbusOnkoBasePS_IsGormun').setValue(false);
                                win.form.findField('MorbusOnkoBasePS_IsImmun').setValue(false);
                                win.form.findField('MorbusOnkoBasePS_IsOther').setValue(false);
                            }
                        }
                    }
                }, {
                    boxLabel: langs('Обследование, лечение не предусмотрено'),
                    hideLabel: true,
                    name: 'MorbusOnkoBasePS_IsNotTreat',
                    anchor: '100%',
                    xtype: 'checkbox',
                    listeners: {
                        change: function(checkbox,checked){
                            if (checked) {
                                win.form.findField('MorbusOnkoBasePS_IsTreatDelay').setValue(false);
                                win.form.findField('MorbusOnkoBasePS_IsSurg').setValue(false);
                                win.form.findField('MorbusOnkoBasePS_IsBeam').setValue(false);
                                win.form.findField('MorbusOnkoBasePS_IsChem').setValue(false);
                                win.form.findField('MorbusOnkoBasePS_IsGormun').setValue(false);
                                win.form.findField('MorbusOnkoBasePS_IsImmun').setValue(false);
                                win.form.findField('MorbusOnkoBasePS_IsOther').setValue(false);
                            }
                        }
                    }
                }, {
                    boxLabel: langs('Хирургическое лечение при госпитализации'),
                    hideLabel: true,
                    name: 'MorbusOnkoBasePS_IsSurg',
                    anchor: '100%',
                    xtype: 'checkbox',
                    listeners: {
                        change: function(checkbox,checked){
                            win.form.findField('MorbusOnkoBasePS_IsPreOper').setDisabled(!checked);
                            win.form.findField('MorbusOnkoBasePS_IsIntraOper').setDisabled(!checked);
                            win.form.findField('MorbusOnkoBasePS_IsPostOper').setDisabled(!checked);
                            if (checked) {
                                win.form.findField('MorbusOnkoBasePS_IsTreatDelay').setValue(false);
                                win.form.findField('MorbusOnkoBasePS_IsNotTreat').setValue(false);
                                win.form.findField('MorbusOnkoBasePS_IsBeam').setValue(false);
                            } else {
                                win.form.findField('MorbusOnkoBasePS_IsPreOper').setValue(false);
                                win.form.findField('MorbusOnkoBasePS_IsIntraOper').setValue(false);
                                win.form.findField('MorbusOnkoBasePS_IsPostOper').setValue(false);
                            }
                        }
                    }
                }, {
                    xtype: 'panel',
                    border: false,
                    frame: false,
                    anchor: '100%',
                    autoHeight: true,
                    style: 'margin-left: 30px;',
                    items: [{
                        boxLabel: langs('Предоперационная лучевая терапия'),
                        hideLabel: true,
                        name: 'MorbusOnkoBasePS_IsPreOper',
                        xtype: 'checkbox',
                        listeners: {
                            change: function(checkbox,checked){
                                if (checked) {
                                    win.form.findField('MorbusOnkoBasePS_IsTreatDelay').setValue(false);
                                    win.form.findField('MorbusOnkoBasePS_IsNotTreat').setValue(false);
                                }
                            }
                        }
                    }, {
                        boxLabel: langs('Интраоперационная лучевая терапия'),
                        hideLabel: true,
                        name: 'MorbusOnkoBasePS_IsIntraOper',
                        xtype: 'checkbox',
                        listeners: {
                            change: function(checkbox,checked){
                                if (checked) {
                                    win.form.findField('MorbusOnkoBasePS_IsTreatDelay').setValue(false);
                                    win.form.findField('MorbusOnkoBasePS_IsNotTreat').setValue(false);
                                }
                            }
                        }
                    }, {
                        boxLabel: langs('Послеоперационная лучевая терапия'),
                        hideLabel: true,
                        name: 'MorbusOnkoBasePS_IsPostOper',
                        xtype: 'checkbox',
                        listeners: {
                            change: function(checkbox,checked){
                                if (checked) {
                                    win.form.findField('MorbusOnkoBasePS_IsTreatDelay').setValue(false);
                                    win.form.findField('MorbusOnkoBasePS_IsNotTreat').setValue(false);
                                }
                            }
                        }
                    }]
                }, {
                    boxLabel: langs('Лучевая терапия'),
                    hideLabel: true,
                    name: 'MorbusOnkoBasePS_IsBeam',
                    anchor: '100%',
                    xtype: 'checkbox',
                    listeners: {
                        change: function(checkbox,checked){
                            if (checked) {
                                win.form.findField('MorbusOnkoBasePS_IsTreatDelay').setValue(false);
                                win.form.findField('MorbusOnkoBasePS_IsNotTreat').setValue(false);
                                win.form.findField('MorbusOnkoBasePS_IsSurg').setValue(false);
                            }
                        }
                    }
                }, {
                    boxLabel: langs('Химиотерапия'),
                    hideLabel: true,
                    name: 'MorbusOnkoBasePS_IsChem',
                    anchor: '100%',
                    xtype: 'checkbox',
                    listeners: {
                        change: function(checkbox,checked){
                            if (checked) {
                                win.form.findField('MorbusOnkoBasePS_IsTreatDelay').setValue(false);
                                win.form.findField('MorbusOnkoBasePS_IsNotTreat').setValue(false);
                            }
                        }
                    }
                }, {
                    boxLabel: langs('Гормонотерапия'),
                    hideLabel: true,
                    name: 'MorbusOnkoBasePS_IsGormun',
                    anchor: '100%',
                    xtype: 'checkbox',
                    listeners: {
                        change: function(checkbox,checked){
                            if (checked) {
                                win.form.findField('MorbusOnkoBasePS_IsTreatDelay').setValue(false);
                                win.form.findField('MorbusOnkoBasePS_IsNotTreat').setValue(false);
                            }
                        }
                    }
                }, {
                    boxLabel: langs('Иммунотерапия'),
                    hideLabel: true,
                    name: 'MorbusOnkoBasePS_IsImmun',
                    anchor: '100%',
                    xtype: 'checkbox',
                    listeners: {
                        change: function(checkbox,checked){
                            if (checked) {
                                win.form.findField('MorbusOnkoBasePS_IsTreatDelay').setValue(false);
                                win.form.findField('MorbusOnkoBasePS_IsNotTreat').setValue(false);
                            }
                        }
                    }
                }, {
                    boxLabel: langs('Другое'),
                    hideLabel: true,
                    name: 'MorbusOnkoBasePS_IsOther',
                    anchor: '100%',
                    xtype: 'checkbox',
                    listeners: {
                        change: function(checkbox,checked){
                            if (checked) {
                                win.form.findField('MorbusOnkoBasePS_IsTreatDelay').setValue(false);
                                win.form.findField('MorbusOnkoBasePS_IsNotTreat').setValue(false);
                            }
                        }
                    }
                }]
            }, {
                fieldLabel: langs('Состояние при выписке'),
                name: 'OnkoLeaveType_id',
                xtype: 'commonSprCombo',
                sortField:'OnkoLeaveType_Code',
                comboSubject: 'OnkoLeaveType',
                width: 700
			}]
		});

        Ext6.apply(win, {
			items: [
				win.PersonInfoPanel, {
                    xtype: 'panel',
					layout: 'form',
					scrollable: true,
					border: false,
					height: 530,
					items: [win.FormPanel]
				}
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