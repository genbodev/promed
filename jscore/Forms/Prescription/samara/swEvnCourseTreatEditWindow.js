/**
* swEvnCourseTreatEditWindow - окно создания/редактирования/просмотра курса назначений c типом Лекарственное лечение.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Prescription
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @version      0.001-15.03.2012
* @comment      Префикс для id компонентов EPRTREF (EvnCourseTreatEditForm)
*				tabIndex: TABINDEX_EVNPRESCR + (от 100 до 129)
*/
/*NO PARSE JSON*/

sw.Promed.swEvnCourseTreatEditWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	objectName: 'swEvnCourseTreatEditWindow',
	objectSrc: '/jscore/Forms/Prescription/swEvnCourseTreatEditWindow.js',

	action: null,
	autoHeight: true,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	collapsible: false,
	doSave: function(options) {
        var base_form = this.FormPanel.getForm();
        
        if (!base_form.findField('EvnCourseTreat_ContReception').getValue()){
            base_form.findField('EvnCourseTreat_ContReception').setValue(base_form.findField('EvnCourseTreat_Duration').getValue());
            this.TreatDrugListPanel.reCountAll();
        }
        

		if ( this.formStatus == 'save' ) {
			return false;
		}

		if ( typeof options != 'object' ) {
			options = {};
		}

		this.formStatus = 'save';

		var base_form = this.FormPanel.getForm();
        var thas = this;
		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
                    thas.formStatus = 'edit';
                    thas.FormPanel.getFirstInvalidEl().focus(true);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
        var loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT_SAVE });
        loadMask.show();

        var params = {};

        params.parentEvnClass_SysNick = this.parentEvnClass_SysNick;
        if(options.signature) {
            params.signature = 1;
        } else {
            params.signature = 0;
        }

        this.TreatDrugListPanel.reCountAll();
        var DrugListData = this.TreatDrugListPanel.getDrugListData();

        if (DrugListData.length==0) {
            this.formStatus = 'edit';
            sw.swMsg.alert('Ошибка', 'В курсе должны быть заполнены поля хотя бы одного медикамента!');
            loadMask.hide();
            return false;
        }
        DrugListData = Ext.util.JSON.encode(DrugListData);
        base_form.findField('DrugListData').setValue(DrugListData);

        if( base_form.findField('DurationType_id').disabled ) {
            params.DurationType_id = base_form.findField('DurationType_id').getValue();
        }

        if( base_form.findField('DurationType_recid').disabled ) {
            params.DurationType_recid = base_form.findField('DurationType_recid').getValue();
        }

        if( base_form.findField('DurationType_intid').disabled ) {
            params.DurationType_intid = base_form.findField('DurationType_intid').getValue();
        }

        if( base_form.findField('PerformanceType_id').disabled ) {
            params.PerformanceType_id = base_form.findField('PerformanceType_id').getValue();
        }

        base_form.submit({
            failure: function(result_form, action) {
                thas.formStatus = 'edit';
                loadMask.hide();

                if ( action.result ) {
                    if ( action.result.Error_Msg ) {
                        sw.swMsg.alert('Ошибка', action.result.Error_Msg);
                    }
                    else {
                        sw.swMsg.alert('Ошибка', 'При сохранении произошли ошибки [Тип ошибки: 1]');
                    }
                }
            },
            params: params,
            success: function(result_form, action) {
                thas.formStatus = 'edit';
                loadMask.hide();

                if ( action.result ) {
                    var data = base_form.getValues();
                    data.EvnCourseTreat_id = action.result.EvnCourseTreat_id;
                    thas.callback(data);
                    thas.hide();
                } else {
                    sw.swMsg.alert('Ошибка', 'При сохранении произошли ошибки [Тип ошибки: 2]');
                }
            }
        });
        return true;
	},
	draggable: true,
	enableEdit: function(enable) { // ipavelpetrov
		var base_form = this.FormPanel.getForm();
		var formFields = [
			,'EvnCourseTreat_setDate'
			,'EvnCourseTreat_CountDay'
			,'EvnCourseTreat_Duration'
			,'EvnCourseTreat_ContReception'
			,'EvnCourseTreat_Interval'
			,'DurationType_id'
			,'DurationType_recid'
			,'DurationType_intid'
            ,'PerformanceType_id'
			,'EvnPrescrTreat_IsCito'
			,'EvnPrescrTreat_Descr'
			,'EvnCourseTreat_IsPrescrInfusion'
			,'PrescriptionIntroType_id' 
			,'PrescriptionTimeType_id' 
			,'PrescriptionTreatOrderType_id'
		];
		for (var i = 0; i < formFields.length; i++ ) {
			if ( enable ) {
				base_form.findField(formFields[i]).enable();
			}
			else {
				base_form.findField(formFields[i]).disable();
			}
		}

        this.TreatDrugListPanel.setEnableEdit(enable);

		if ( enable ) {
			this.buttons[0].show();
			//Заведение графика сделать возможным только в днях
			if(this.parentEvnClass_SysNick == 'EvnSection') {
				base_form.findField('DurationType_id').setValue(1);
				base_form.findField('DurationType_recid').setValue(1);
				base_form.findField('DurationType_intid').setValue(1);
				base_form.findField('PerformanceType_id').setValue(2);
				base_form.findField('DurationType_id').disable();
				base_form.findField('DurationType_recid').disable();
				base_form.findField('DurationType_intid').disable();
				base_form.findField('PerformanceType_id').disable();
			}
		}
		else {
			this.buttons[0].hide();
		}
	},
	formStatus: 'edit',
	id: 'EvnCourseTreatEditWindow',
	initComponent: function() {
        var thas = this;

        this.itemBodyStyle = 'padding: 5px 5px 0';
        this.labelAlign = 'right';
        this.labelWidth = 130;

		this.TreatDrugListPanel = new sw.Promed.TreatDrugListPanel({
			win: this,
			form_id: 'EvnCourseTreatEditForm',
            objectDrug: 'EvnCourseTreatDrug',
            itemBodyStyle: this.itemBodyStyle ,
            labelAlign: this.labelAlign,
            labelWidth: this.labelWidth,
		    defaultMethodInputDrug_id: 2,
            getRegimeFormParams: function() {
                var base_form = thas.FormPanel.getForm();
                return {
                    setDate: base_form.findField('EvnCourseTreat_setDate').getRawValue(),
                    CountDay: base_form.findField('EvnCourseTreat_CountDay').getValue(),
                    Duration: base_form.findField('EvnCourseTreat_Duration').getValue(),
                    DurationType_id: base_form.findField('DurationType_id').getValue(),
                    DurationType_Nick: base_form.findField('DurationType_id').getRawValue(),
                    ContReception: base_form.findField('EvnCourseTreat_ContReception').getValue(),
                    DurationType_recid: base_form.findField('DurationType_recid').getValue(),
                    Interval: base_form.findField('EvnCourseTreat_Interval').getValue(),
                    DurationType_intid: base_form.findField('DurationType_intid').getValue()
                };
			}
		});
		
		this.FormPanel = new Ext.form.FormPanel({
			autoHeight: true,
			bodyBorder: false,
			border: false,
			frame: false,
			id: 'EvnCourseTreatEditForm',
			reader: new Ext.data.JsonReader({
				success: Ext.emptyFn
			},  [
				{ name: 'accessType' },
				{ name: 'DrugListData' },

                { name: 'EvnCourseTreat_id' },
                { name: 'EvnCourseTreat_pid' },
                { name: 'MedPersonal_id' },
                { name: 'LpuSection_id' },
                { name: 'Morbus_id' },
				{ name: 'EvnCourseTreat_setDate' },
                { name: 'EvnCourseTreat_CountDay' },
                //{ name: 'EvnCourseTreat_MaxCountDay' },
                //{ name: 'EvnCourseTreat_MinCountDay' },
				{ name: 'EvnCourseTreat_Duration' },
                { name: 'DurationType_id' },
				{ name: 'EvnCourseTreat_ContReception' },
                { name: 'DurationType_recid' },
				{ name: 'EvnCourseTreat_Interval' },
                { name: 'DurationType_intid' },
                //{ name: 'EvnCourseTreat_FactCount' },
                //{ name: 'EvnCourseTreat_PrescrCount' },
                //{ name: 'ResultDesease_id' },
                { name: 'PrescriptionIntroType_id' },
                { name: 'PrescriptionTreatType_id' },
				{ name: 'PrescriptionTimeType_id' },
				{ name: 'PrescriptionTreatOrderType_id' },
				{ name: 'EvnCourseTreat_IsPrescrInfusion' },
				
                //{ name: 'PrescriptionStatusType_id' },
				{ name: 'PerformanceType_id' },

				{ name: 'EvnPrescrTreat_IsCito' },
				{ name: 'EvnPrescrTreat_Descr' },
				{ name: 'PersonEvn_id' },
				{ name: 'Server_id' }
			]),
			region: 'center',
			url: '/?c=EvnPrescr&m=saveEvnCourseTreat',

			items: [{
                name: 'accessType', // Режим доступа
                value: '',
                xtype: 'hidden'
            }, {
                name: 'DrugListData',
                value: '',
                xtype: 'hidden'
            }, {
				name: 'EvnCourseTreat_id', // Идентификатор курса
				value: null,
				xtype: 'hidden'
			}, {
				name: 'EvnCourseTreat_pid', // Идентификатор события
				value: null,
				xtype: 'hidden'
			}, {
                name: 'MedPersonal_id',
                value: null,
                xtype: 'hidden'
            }, {
                name: 'LpuSection_id',
                value: null,
                xtype: 'hidden'
            }, {
                name: 'Morbus_id',
                value: null,
                xtype: 'hidden'
            }, {
                name: 'PrescriptionTreatType_id',
                value: null,
                xtype: 'hidden'
            }, {
				name: 'PersonEvn_id', // Идентификатор состояния человека
				value: null,
				xtype: 'hidden'
			}, {
				name: 'Server_id', // Идентификатор сервера
				value: null,
				xtype: 'hidden'
			}, {
				name: 'PrescriptionStatusType_id', // Идентификатор (Рабочее,Подписанное,Отмененное)
				value: null,
				xtype: 'hidden'
			},
			this.TreatDrugListPanel,
			{
                autoHeight: true,
                bodyBorder: false,
                bodyStyle: this.itemBodyStyle,
                border: false,
                frame: false,
                labelAlign: this.labelAlign,
                labelWidth: this.labelWidth,
                layout: 'form',
                items: [{
                    allowBlank: false,
                    fieldLabel: 'Инфузия',
                    hiddenName: 'EvnCourseTreat_IsPrescrInfusion',
                    name: 'EvnCourseTreat_IsPrescrInfusion',
                    tabIndex: TABINDEX_EVNPRESCR + 104,
                    //value: 1,
                    width: 100,
                    xtype: 'swyesnocombo'
                }, {
                    allowblank: true,
                    comboSubject: 'PrescriptionIntroType',
                    typeCode: 'int',
                    fieldLabel: 'Способ применения',
                    width: 370,
                    tabIndex: TABINDEX_EVNPRESCR + 105,
                    listeners: {
                        change: function(combo, newValue) {
                            combo.fireEvent('select', combo, combo.getStore().getById(newValue));
                        },
                        select: function(combo, record) {
                            var base_form = thas.FormPanel.getForm();

                            if(record && record.get('PrescriptionIntroType_Code').toString().inlist([null,'','0','1','2','3','4','12','13'])) {
                                base_form.findField('PrescriptionTreatOrderType_id').enable();
                            }
                            if(record && record.get('PrescriptionIntroType_Code').toString().inlist(['5','6','7','8','9','10','11'])) {
                                base_form.findField('PrescriptionTreatOrderType_id').clearValue();
                                base_form.findField('PrescriptionTreatOrderType_id').disable();
                            }


                            if(thas.parentEvnClass_SysNick == 'EvnSection') {
                                return true;
                            }

                            if(record && record.get('PrescriptionIntroType_Code').toString().inlist(['1','2','3','4','12','13'])) {
                                base_form.findField('PerformanceType_id').setValue(1);
                            }
                            if(record && record.get('PrescriptionIntroType_Code').toString().inlist(['7','8','9','10','11'])) {
                                base_form.findField('PerformanceType_id').setValue(2);
                            }
                            if(record && record.get('PrescriptionIntroType_Code').toString().inlist(['5','6'])) {
                                base_form.findField('PerformanceType_id').setValue(3);
                            }
                            return true;
                        }
                    },
                    xtype: 'swcommonsprcombo'
                }, {
                    fieldLabel: 'Начать',
                    format: 'd.m.Y',
                    allowBlank: false,
                    name: 'EvnCourseTreat_setDate',
                    plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
                    selectOnFocus: true,
                    width: 100,
                    tabIndex: TABINDEX_EVNPRESCR + 111,
                    listeners: {
                        change: function() {
                            thas.TreatDrugListPanel.reCountAll();
                        }
                    },
                    xtype: 'swdatefield'
                }, {
                    allowDecimals: false,
                    allowNegative: false,
                    fieldLabel: 'Приемов в сутки',
                    value: 1,
                    minValue: 1,
                    style: 'text-align: right;',
                    name: 'EvnCourseTreat_CountDay',
                    width: 100,
                    tabIndex: TABINDEX_EVNPRESCR + 112,
                    listeners: {
                        change: function() {
                            thas.TreatDrugListPanel.reCountAll();
                        }
                    },
                    xtype: 'numberfield'
                }, 
				{
                    border: false,
                    layout: 'column',
                    items: [{
                        border: false,
                        labelWidth: 130,
                        layout: 'form',
                        items: [{
							allowblank: true,
							comboSubject: 'PrescriptionTimeType',
							typeCode: 'int',
							fieldLabel: 'Время приема',
							width: 160,
							tabIndex: TABINDEX_EVNPRESCR + 112,
							xtype: 'swcommonsprcombo'				
						}]
						}, {
                        border: false,
                        labelWidth: 100,
                        layout: 'form',
                        items: [{
							allowblank: true,
							comboSubject: 'PrescriptionTreatOrderType',
							typeCode: 'int',
							fieldLabel: 'Порядок приема',
							width: 110,
							tabIndex: TABINDEX_EVNPRESCR + 112,
							xtype: 'swcommonsprcombo'				
						}]
					}]
				}, {
                    border: false,
                    layout: 'column',
                    items: [{
                        border: false,
                        labelWidth: 130,
                        layout: 'form',
                        items: [{
                            allowDecimals: false,
                            allowNegative: false,
                            fieldLabel: 'Продолжительность',//Продолжительность курса
//                            value: 1,
//                            minValue: 1,
                            style: 'text-align: right;',
                            name: 'EvnCourseTreat_Duration',
                            width: 100,
                            tabIndex: TABINDEX_EVNPRESCR + 114,
                            listeners: {
                                change: function(field, newValue) {
                                    var base_form = thas.FormPanel.getForm();
                                    base_form.findField('EvnCourseTreat_ContReception').setValue(newValue);
                                    thas.TreatDrugListPanel.reCountAll();
                                }
                            },
                            xtype: 'numberfield'
                        }]
                    },{
                        border: false,
                        layout: 'form',
                        style: 'margin-left: 10px; padding: 0px;',
                        items: [{
                            hiddenName: 'DurationType_id',//Тип продолжительности
                            width: 70,
                            value: 1,
                            tabIndex: TABINDEX_EVNPRESCR + 115,
                            listeners: {
                                change: function(combo, newValue) {
                                    var base_form = thas.FormPanel.getForm();
                                    var record = combo.getStore().getById(newValue);
                                    if ( !record ) {
                                        return false;
                                    }
                                    base_form.findField('DurationType_recid').setValue(newValue);
                                    base_form.findField('DurationType_intid').setValue(newValue);
                                    thas.TreatDrugListPanel.reCountAll();
                                    return true;
                                }
                            },
                            xtype: 'swdurationtypecombo'
                        }]
                    }]
                }, {
                    fieldLabel: 'Комментарий',
                    height: 70,
                    name: 'EvnPrescrTreat_Descr',
                    width: 370,
                    tabIndex: TABINDEX_EVNPRESCR + 123,
                    xtype: 'textarea'
                },
                new sw.Promed.Panel({
                    autoHeight: true,
                    border: true,
                    collapsible: true,
                    collapsed: true,
                    layout: 'form',
//                    listeners: {
//                        'expand': function(panel) {
//                            // this.findById('EvnPSEditForm').getForm().findField('EvnPS_IsCont').focus(true);
//                        }.createDelegate(this)
//                    },
                    style: 'margin-bottom: 0.5em;',
                    title: 'Подробнее',
                		items: [{
                            border: false,
                            layout: 'column',
                            items: [{
                                border: false,
                                labelWidth: 130,
                                layout: 'form',
                                items: [{
                                    allowDecimals: false,
                                    allowNegative: false,
                                    fieldLabel: 'Непрерывный прием',
                                    value: 1,
                                    minValue: 1,
                                    style: 'text-align: right;',
                                    name: 'EvnCourseTreat_ContReception',
                                    width: 100,
                                    tabIndex: TABINDEX_EVNPRESCR + 116,
                                    listeners: {
                                        change: function() {
                                            thas.TreatDrugListPanel.reCountAll();
                                        }
                                    },
                                    xtype: 'numberfield'
                                },{
                                    allowDecimals: false,
                                    allowNegative: false,
                                    fieldLabel: 'Перерыв',
                                    value: 0,
                                    minValue: 0,
                                    style: 'text-align: right;',
                                    name: 'EvnCourseTreat_Interval',
                                    width: 100,
                                    tabIndex: TABINDEX_EVNPRESCR + 118,
                                    listeners: {
                                        change: function() {
                                            thas.TreatDrugListPanel.reCountAll();
                                        }
                                    },
                                    xtype: 'numberfield'
                                }]
                            },{
                                border: false,
                                layout: 'form',
                                style: 'margin-left: 10px; padding: 0px;',
                                items: [{
                                    hiddenName: 'DurationType_recid',//Тип Непрерывный прием
                                    width: 70,
                                    value: 1,
                                    tabIndex: TABINDEX_EVNPRESCR + 117,
                                    listeners: {
                                        change: function() {
                                            thas.TreatDrugListPanel.reCountAll();
                                        }
                                    },
                                    xtype: 'swdurationtypecombo'
                                },{
                                    hiddenName: 'DurationType_intid',//Тип Перерыв
                                    width: 70,
                                    value: 1,
                                    tabIndex: TABINDEX_EVNPRESCR + 119,
                                    listeners: {
                                        change: function() {
                                            thas.TreatDrugListPanel.reCountAll();
                                        }
                                    },
                                    xtype: 'swdurationtypecombo'
                                }]
                            }]
                        },{
                            comboSubject: 'PerformanceType',
                            fieldLabel: 'Исполнение',
                            width: 370,
                            tabIndex: TABINDEX_EVNPRESCR + 121,
                            xtype: 'swcommonsprcombo'
                        },{ 
		                    boxLabel: 'Cito',
		                    checked: false,
		                    fieldLabel: '',
		                    labelSeparator: '',
		                    name: 'EvnPrescrTreat_IsCito',
		                    tabIndex: TABINDEX_EVNPRESCR + 122,
		                    xtype: 'checkbox'
		                }
		                ]})
                ]
            }]
		});

		Ext.apply(this, {
			buttons: [{
				handler: function() {
                    thas.doSave();
				},
				iconCls: 'save16',
				tabIndex: TABINDEX_EVNPRESCR + 125,
				text: BTN_FRMSAVE
			}, {
				hidden: true,
                handler: function() {
                    thas.doSave({signature: true});
				},
				iconCls: 'signature16',
				tabIndex: TABINDEX_EVNPRESCR + 126,
				text: BTN_FRMSIGN
			}, {
				text: '-'
			},
			//HelpButton(this, -1),
			{
				handler: function() {
                    thas.hide();
				},
				iconCls: 'cancel16',
				onTabAction: function () {
                    //thas.FormPanel.getForm().findField('MethodInputDrug_id').focus(true, 250);
				},
				tabIndex: TABINDEX_EVNPRESCR + 129,
				text: BTN_FRMCANCEL
			}],
			items: [
				this.FormPanel
			],
			layout: 'form'
		});

		sw.Promed.swEvnCourseTreatEditWindow.superclass.initComponent.apply(this, arguments);
	},
	keys: [{
		alt: true,
		fn: function(inp, e) {
			var current_window = Ext.getCmp('EvnCourseTreatEditWindow');

			switch ( e.getKey() ) {
				case Ext.EventObject.C:
					current_window.doSave();
				break;

				case Ext.EventObject.J:
					current_window.hide();
				break;
			}
		},
		key: [
			Ext.EventObject.C,
			Ext.EventObject.J
		],
		scope: this,
		stopEvent: false
	}],
	layout: 'form',
	listeners: {
		hide: function(win) {
			win.onHide();
		}
	},
	loadMask: null,
	maximizable: false,
	maximized: false,
	modal: true,
	onHide: Ext.emptyFn,
	plain: true,
	resizable: false,
	show: function() {
		sw.Promed.swEvnCourseTreatEditWindow.superclass.show.apply(this, arguments);

        var thas = this;

		var base_form = this.FormPanel.getForm();
		base_form.reset();
        this.TreatDrugListPanel.reset();

		this.parentEvnClass_SysNick = null;
		this.action = null;
		this.callback = Ext.emptyFn;
		this.formStatus = 'edit';
		this.onHide = Ext.emptyFn;
		
		if ( !arguments[0] || !arguments[0].formParams ) {
			sw.swMsg.alert('Сообщение', 'Неверные параметры', function() { thas.hide(); } );
			return false;
		}

		base_form.setValues(arguments[0].formParams);

		if ( arguments[0].action && typeof arguments[0].action == 'string' ) {
			this.action = arguments[0].action;
		}

		if ( arguments[0].parentEvnClass_SysNick && typeof arguments[0].parentEvnClass_SysNick == 'string' ) {
			this.parentEvnClass_SysNick = arguments[0].parentEvnClass_SysNick;
		}

		if ( arguments[0].callback && typeof arguments[0].callback == 'function' ) {
			this.callback = arguments[0].callback;
		}

		if ( arguments[0].onHide && typeof arguments[0].onHide == 'function' ) {
			this.onHide = arguments[0].onHide;
		}

		this.getLoadMask(LOAD_WAIT).show();

		switch ( this.action ) {
			case 'add':
				this.getLoadMask().hide();
				base_form.clearInvalid();
				this.setTitle('Курс лекарственного лечения: Добавление');
				this.enableEdit(true);
                //чтобы выбирать с остатков отделения
                this.TreatDrugListPanel.parentEvnClass_SysNick = this.parentEvnClass_SysNick;
                this.TreatDrugListPanel.LpuSection_id = base_form.findField('LpuSection_id').getValue();
			    // ipavelpetrov
                base_form.findField('EvnCourseTreat_setDate').setValue(arguments[0].formParams.begDate);
				this.TreatDrugListPanel.onLoadForm();
                if (base_form.findField('MethodInputDrug_id0')) {
                    base_form.findField('MethodInputDrug_id0').focus(true, 250);
                }
			break;
			case 'edit':
			case 'view':
				base_form.load({
					failure: function() {
                        thas.getLoadMask().hide();
						sw.swMsg.alert('Ошибка', 'Ошибка при загрузке данных формы', function() { thas.hide(); } );
					},
					params: {
						EvnCourseTreat_id: base_form.findField('EvnCourseTreat_id').getValue(),
						parentEvnClass_SysNick: this.parentEvnClass_SysNick
					},
					success: function() {
                        thas.getLoadMask().hide();
						base_form.clearInvalid();
						if ( base_form.findField('accessType').getValue() == 'view' ) {
                            thas.action = 'view';
						}
						if ( thas.action == 'edit' ) {
                            thas.setTitle('Курс лекарственного лечения: Редактирование');
                            thas.enableEdit(true);
						} else {
                            thas.setTitle('Курс лекарственного лечения: Просмотр');
                            thas.enableEdit(false);
						}

                        //чтобы выбирать с остатков отделения
                        thas.TreatDrugListPanel.parentEvnClass_SysNick = thas.parentEvnClass_SysNick;
                        thas.TreatDrugListPanel.LpuSection_id = base_form.findField('LpuSection_id').getValue();
                        thas.TreatDrugListPanel.onLoadForm(base_form.findField('DrugListData').getValue());

						if ( thas.action == 'edit' ) {
							if ( base_form.findField('MethodInputDrug_id0') ) {
                                base_form.findField('MethodInputDrug_id0').focus(true, 250);
                            }
						} else {
                            thas.buttons[thas.buttons.length - 1].focus();
						}
					},
					url: '/?c=EvnPrescr&m=loadEvnCourseTreatEditForm'
				});
			break;

			default:
				this.getLoadMask().hide();
				this.hide();
			break;
		}

        this.center();
        return true;
	},
	width: 550
});