/**
* swPolkaEvnPrescrTreatEditWindow - окно создания курса/редактирования назначения c типом Лекарственное лечение.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Prescription
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @version      0.001-15.03.2012
* @comment      Префикс для id компонентов EPRTREF (PolkaEvnPrescrTreatEditForm)
*				tabIndex: TABINDEX_EVNPRESCR + (от 100 до 129)
*/
/*NO PARSE JSON*/

sw.Promed.swPolkaEvnPrescrTreatEditWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	objectName: 'swPolkaEvnPrescrTreatEditWindow',
	objectSrc: '/jscore/Forms/Prescription/swPolkaEvnPrescrTreatEditWindow.js',

	action: null,
	autoHeight: true,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	collapsible: false,
	doSave: function(options) {
		if ( this.formStatus == 'save' ) {
			return false;
		}

		if ( typeof options != 'object' ) {
			options = new Object();
		}

		this.formStatus = 'save';

		var base_form = this.FormPanel.getForm();

		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					this.formStatus = 'edit';
					this.FormPanel.getFirstInvalidEl().focus(true);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
		if(base_form.findField('EvnPrescrTreatDrug_Kolvo').getValue()>0||base_form.findField('EvnPrescrTreatDrug_KolvoEd').getValue()>0){
			var loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT_SAVE });
			loadMask.show();

			var params = new Object();
			params.parentEvnClass_SysNick = this.parentEvnClass_SysNick;
			if(options.signature) {
				params.signature = 1;
			} else {
				params.signature = 0;
			}

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

			if( base_form.findField('EvnCourseTreatDrug_MaxDoseDay').disabled ) {
				params.EvnCourseTreatDrug_MaxDoseDay = base_form.findField('EvnCourseTreatDrug_MaxDoseDay').getValue();
			}

			if( base_form.findField('EvnCourseTreatDrug_PrescrDose').disabled ) {
				params.EvnCourseTreatDrug_PrescrDose = base_form.findField('EvnCourseTreatDrug_PrescrDose').getValue();
			}
			if( base_form.findField('EvnCourseTreatDrug_MinDoseDay').disabled ) {
				params.EvnCourseTreatDrug_MinDoseDay = base_form.findField('EvnCourseTreatDrug_MinDoseDay').getValue();
			}

			if( base_form.findField('EvnCourseTreatDrug_PrescrDose').disabled ) {
				params.EvnCourseTreatDrug_FactDose = base_form.findField('EvnCourseTreatDrug_FactDose').getValue();
			}

			base_form.submit({
				failure: function(result_form, action) {
					this.formStatus = 'edit';
					loadMask.hide();

					if ( action.result ) {
						if ( action.result.Error_Msg ) {
							sw.swMsg.alert(lang['oshibka'], action.result.Error_Msg);
						}
						else {
							sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_1]']);
						}
					}
				}.createDelegate(this),
				params: params,
				success: function(result_form, action) {
					this.formStatus = 'edit';
					loadMask.hide();

					if ( action.result ) {
						var data = new Object();
						data.evnPrescrTreatData = base_form.getValues();
						data.evnPrescrTreatData.EvnPrescrTreat_id = action.result.EvnPrescrTreat_id;
						this.callback(data);
						this.hide();
					}
					else {
						sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_2]']);
					}
				}.createDelegate(this)
			});
		}else{
			this.formStatus = 'edit';
			sw.swMsg.alert(lang['oshibka'], lang['pole_ed_dozirovki_ili_ed_izmereniya_doljno_byit_zapolneno']);
			return false;
		}
	},
	draggable: true,
	setDrugForm: function(drugform_name) {
		var base_form = this.FormPanel.getForm();
		if(!drugform_name) drugform_name = base_form.findField('DrugForm_Name').getValue();
		var drugform_nick = '';

		if(typeof drugform_name == 'string') {
			drugform_name = drugform_name.toLowerCase();
			if(drugform_name.indexOf(lang['kapl']) >= 0) {
				drugform_nick = lang['kapli'];
			}
			if(drugform_name.indexOf(lang['kaps']) >= 0) {
				drugform_nick = lang['kaps'];
			}
			if(drugform_name.indexOf(lang['supp']) >= 0) {
				drugform_nick = lang['supp'];
			}
			if(drugform_name.indexOf(lang['tabl']) >= 0) {
				drugform_nick = lang['tabl'];
			}

			this.findById('EPRTREF_DrugForm_Nick').setText(drugform_nick);
		}
	},
	enableEdit: function(enable) {
		var base_form = this.FormPanel.getForm();
		var formFields = [
			/*'DrugPrepFas_id'
			,*/'Drug_id'
			,'DrugComplexMnn_id'
			,'MethodInputDrug_id'
			,'PrescriptionIntroType_id'
			,'EdUnits_id'
			,'EvnPrescrTreatDrug_Kolvo'
			,'EvnPrescrTreatDrug_KolvoEd'
			//,'EvnPrescrTreatDrug_Kolvo_Show'
			,'EvnPrescrTreat_setDate'
			,'EvnCourseTreat_MaxCountDay'
			,'EvnCourseTreat_Duration'
			,'EvnCourseTreat_ContReception'
			,'EvnCourseTreat_Interval'
			,'DurationType_id'
			,'DurationType_recid'
			,'DurationType_intid'
            ,'PerformanceType_id'
            ,'EvnCourseTreatDrug_PrescrDose'
            ,'EvnCourseTreatDrug_MaxDoseDay'
			,'EvnCourseTreatDrug_FactDose'
            ,'EvnCourseTreatDrug_MinDoseDay'
			,'EvnPrescrTreat_IsCito'
			,'EvnPrescrTreat_Descr'
		];
		var i = 0;

		for ( i = 0; i < formFields.length; i++ ) {
			if ( enable ) {
				base_form.findField(formFields[i]).enable();
			}
			else {
				base_form.findField(formFields[i]).disable();
			}
		}

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
	id: 'PolkaEvnPrescrTreatEditWindow',
	initComponent: function() {
        var thas = this;

		this.DrugPanel = new sw.Promed.swDrugPanel({
			win: this,
			form_id: 'PolkaEvnPrescrTreatEditForm',
			firstTabIndex: TABINDEX_EVNPRESCR + 100,
			labelWidth: 130,
			bodyStyle: 'padding: 0',
			onClearDrug: function(combo, record){
                thas.findById('EPRTREF_DrugForm_Nick').setText('');
                var base_form = thas.FormPanel.getForm();
                var fieldCountInDay = base_form.findField('EvnCourseTreat_MaxCountDay');
                fieldCountInDay.fireEvent('change', fieldCountInDay, fieldCountInDay.getValue());
            },
			onSelectDrug: function(combo, record){
				var drugform_name;
				if(record.get('DrugPrep_Name')) {
					drugform_name = record.get('DrugPrep_Name');
				}
				if(record.get('DrugTorg_Name')) {
					drugform_name = record.get('DrugTorg_Name');
				}
				if(record.get('DrugForm_Name')) {
					drugform_name = record.get('DrugForm_Name');
				}
				if(record.get('DrugComplexMnn_Name')) {
					drugform_name = record.get('DrugComplexMnn_Name');
				}
                thas.setDrugForm(drugform_name);
                var base_form = thas.FormPanel.getForm();
                var fieldCountInDay = base_form.findField('EvnCourseTreat_MaxCountDay');
                fieldCountInDay.fireEvent('change', fieldCountInDay, fieldCountInDay.getValue());
			}
		});
		
		this.FormPanel = new Ext.form.FormPanel({
			autoHeight: true,
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			border: false,
			frame: false,
			id: 'PolkaEvnPrescrTreatEditForm',
			labelAlign: 'right',
			labelWidth: 130,
			reader: new Ext.data.JsonReader({
				success: Ext.emptyFn
			},  [
				{ name: 'accessType' },
				{ name: 'EvnPrescrTreat_id' },
                { name: 'EvnPrescrTreat_pid' },
                { name: 'EvnCourse_id' },
                { name: 'MedPersonal_id' },
                { name: 'LpuSection_id' },
                { name: 'Morbus_id' },
                { name: 'PrescriptionTreatType_id' },
                { name: 'PrescriptionStatusType_id' },
				{ name: 'EvnPrescrTreatDrug_id' },
				{ name: 'MethodInputDrug_id' },
				{ name: 'DrugComplexMnn_id' },
				{ name: 'Drug_id' },
				{ name: 'PrescriptionIntroType_id' },
				{ name: 'EvnPrescrTreatDrug_KolvoEd' },
				{ name: 'EvnPrescrTreatDrug_Kolvo' },
				{ name: 'EdUnits_id' },
				{ name: 'DrugForm_Name' },
				{ name: 'EvnPrescrTreat_setDate' },
				{ name: 'EvnCourseTreat_MaxCountDay' },
				{ name: 'EvnCourseTreat_Duration' },
				{ name: 'EvnCourseTreat_ContReception' },
				{ name: 'EvnCourseTreat_Interval' },
				{ name: 'DurationType_id' },
				{ name: 'DurationType_recid' },
				{ name: 'DurationType_intid' },
				{ name: 'PerformanceType_id' },
				{ name: 'EvnPrescrTreat_IsCito' },
				{ name: 'EvnPrescrTreat_Descr' },
				{ name: 'PersonEvn_id' },
				{ name: 'Server_id' }
			]),
			region: 'center',
			url: '/?c=EvnPrescr&m=savePolkaEvnPrescrTreat',

			items: [{
				name: 'accessType', // Режим доступа
				value: '',
				xtype: 'hidden'
			}, {
				name: 'EvnPrescrTreat_id', // Идентификатор назначения
				value: null,
				xtype: 'hidden'
			}, {
				name: 'EvnPrescrTreat_pid', // Идентификатор события
				value: null,
				xtype: 'hidden'
			}, {
                name: 'EvnCourse_id', // Идентификатор курса
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
                name: 'EvnCourseTreat_MinCountDay',
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
			}, {
				name: 'EvnPrescrTreatDrug_id',
				value: null,
				xtype: 'hidden'
			}, {
				name: 'DrugForm_Name',
				value: null,
				xtype: 'hidden'
			},
			this.DrugPanel,
			{
				comboSubject: 'PrescriptionIntroType',
				typeCode: 'int',
				fieldLabel: lang['sposob_primeneniya'],
				width: 370,
				tabIndex: TABINDEX_EVNPRESCR + 105,
				listeners: {
					'change': function(combo, newValue, oldValue) {
						combo.fireEvent('select', combo, combo.getStore().getById(newValue));
					}.createDelegate(this),
					'select': function(combo, record) {
						if(this.parentEvnClass_SysNick == 'EvnSection') {
							return true;
						}
						var base_form = this.FormPanel.getForm();
						if(record && record.get('PrescriptionIntroType_Code').toString().inlist(['1','2','3','4','12','13'])) {
							base_form.findField('PerformanceType_id').setValue(1);
						}
						if(record && record.get('PrescriptionIntroType_Code').toString().inlist(['7','8','9','10','11'])) {
							base_form.findField('PerformanceType_id').setValue(2);
						}
						if(record && record.get('PrescriptionIntroType_Code').toString().inlist(['5','6'])) {
							base_form.findField('PerformanceType_id').setValue(3);
						}
					}.createDelegate(this)
				},
				xtype: 'swcommonsprcombo'
			}, {
				autoHeight: true,
				//labelWidth: 1,
				//style: 'margin-left: 165px; padding: 0px;',
				title: lang['na_odin_priem'],
				//width: 500,
				xtype: 'fieldset',
				items: [{
					border: false,
					layout: 'column',
					items: [{
						border: false,
						labelWidth: 125,
						layout: 'form',
						items: [{
							allowDecimals: true,
							allowNegative: false,
							decimalPrecision: 2,
							fieldLabel: lang['ed_dozirovki'],
							style: 'text-align: right;',
							tabIndex: TABINDEX_EVNPRESCR + 107,
                            listeners: {
                                change: function() {
                                    var base_form = thas.FormPanel.getForm();
                                    var fieldCountInDay = base_form.findField('EvnCourseTreat_MaxCountDay');
                                    fieldCountInDay.fireEvent('change', fieldCountInDay, fieldCountInDay.getValue());
                                }
                            },
							name: 'EvnPrescrTreatDrug_KolvoEd',
							width: 60,
							xtype: 'numberfield'
						}]
					},{
						border: false,
						layout: 'form',
						items: [
							new Ext.form.Label({
								id: 'EPRTREF_DrugForm_Nick',
								style: 'padding: 0; padding-left: 5px; font-size: 9pt;',
								width: 60,
								text: '',
								html: ''
							})
						]
					},{
						border: false,
						layout: 'form',
						items: [{
                            //allowBlank: false,
							allowDecimals: true,
							allowNegative: false,
							decimalPrecision: 4,
							fieldLabel: lang['ed_izmereniya'],
							tabIndex: TABINDEX_EVNPRESCR + 108,
							style: 'text-align: right;',
                            listeners: {
                                change: function() {
                                    var base_form = thas.FormPanel.getForm();
                                    var fieldCountInDay = base_form.findField('EvnCourseTreat_MaxCountDay');
                                    fieldCountInDay.fireEvent('change', fieldCountInDay, fieldCountInDay.getValue());
                                }
                            },
							minValue: 0.0001,
							name: 'EvnPrescrTreatDrug_Kolvo',
							width: 60,
							xtype: 'numberfield'
						}]
					},{
						border: false,
						layout: 'form',
						items: [{
                            //allowBlank: false,
							hideLabel: true,
							width: 60,
							tabIndex: TABINDEX_EVNPRESCR + 109,
                            listeners: {
                                change: function() {
                                    var base_form = thas.FormPanel.getForm();
                                    var fieldCountInDay = base_form.findField('EvnCourseTreat_MaxCountDay');
                                    fieldCountInDay.fireEvent('change', fieldCountInDay, fieldCountInDay.getValue());
                                }
                            },
							xtype: 'swedunitscombo'
						}]
					}]
				}]
			}, {
				fieldLabel: lang['nachat'],
				format: 'd.m.Y',
				allowBlank: false,
				name: 'EvnPrescrTreat_setDate',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				selectOnFocus: true,
				width: 100,
				tabIndex: TABINDEX_EVNPRESCR + 111,
				xtype: 'swdatefield'
			}, {
				allowDecimals: false,
				allowNegative: false,
				fieldLabel: lang['priemov_v_sutki'],
				value: 1,
				minValue: 1,
				style: 'text-align: right;', 
				name: 'EvnCourseTreat_MaxCountDay',
				width: 100,
				tabIndex: TABINDEX_EVNPRESCR + 112,
                listeners: {
                    change: function(fieldCountInDay, newValue) {
                        //log(thas.DrugPanel.selectedDrug);
                        var base_form = thas.FormPanel.getForm();
                        var fieldKolvoEd = base_form.findField('EvnPrescrTreatDrug_KolvoEd'),
                            fieldOkei = base_form.findField('EdUnits_id'),
                            fieldKolvo = base_form.findField('EvnPrescrTreatDrug_Kolvo'),
                            fieldCourseDuration = base_form.findField('EvnCourseTreat_Duration'),
                            fieldCourseDurationType = base_form.findField('DurationType_id'),
                            fieldContReception = base_form.findField('EvnCourseTreat_ContReception'),
                            fieldContReceptionType = base_form.findField('DurationType_recid'),
                            fieldInterval = base_form.findField('EvnCourseTreat_Interval'),
                            fieldIntervalType = base_form.findField('DurationType_intid');
                        // Расчет суточной и курсовой доз
                        var dd_text='', kd_text='', dd=0, kd=0, ed = '';
                        //Дневная доза – Прием в ед. измерения (либо количество ед. дозировки*дозировку)*Приемов в сутки
                        if (fieldKolvo.getValue()
                            && fieldOkei.getValue() && !fieldKolvoEd.getValue()
                        ) {
                            // в ед. измерения
                            dd = newValue*fieldKolvo.getValue();
                            var rec = fieldOkei.getStore().getById(fieldOkei.getValue());
                            if (rec) {
                                ed = rec.get('EdUnits_Name');
                                dd_text = dd.toFixed(2) +' '+ ed;
                            }
                        } if (fieldKolvoEd.getValue() && !fieldKolvo.getValue()) {
                            // в ед. дозировки
                            dd = newValue*fieldKolvoEd.getValue();
                            ed = thas.findById('EPRTREF_DrugForm_Nick').text;
                            dd_text = dd.toFixed(2) +' '+ ed;
                        } if(fieldKolvo.getValue()
                            && fieldOkei.getValue() && fieldKolvoEd.getValue())
							{
								 // в ед. измерения
                            dd = newValue*fieldKolvo.getValue()*fieldKolvoEd.getValue();
                            var rec = fieldOkei.getStore().getById(fieldOkei.getValue());
                            if (rec) {
                                ed = rec.get('EdUnits_Name');
                                dd_text = dd.toFixed(2) +' '+ ed;
                            }
								
							}
                        if (dd > 0 && fieldCourseDuration.getValue()>0 && fieldContReception.getValue()>0) {
                            var duration = fieldCourseDuration.getValue(),
                                cnst = fieldContReception.getValue(),
                                interval = fieldInterval.getValue();
                            switch (true) {
                                case (fieldCourseDurationType.getValue() == 2): duration *= 7; break;
                                case (fieldCourseDurationType.getValue() == 3): duration *= 30; break;
                                case (fieldContReceptionType.getValue() == 2): cnst *= 7; break;
                                case (fieldContReceptionType.getValue() == 3): cnst *= 30; break;
                                case (interval > 0 && fieldIntervalType.getValue() == 2): interval *= 7; break;
                                case (interval > 0 && fieldIntervalType.getValue() == 3): interval *= 30; break;
                            }
                            if (interval > 0) {
                                //это неправильно считает
                                //kd = dd*cnst*duration/(interval+cnst);
                                kd = dd*(duration-(interval*Math.floor(duration/(interval+cnst))));
                            } else {
                                kd = dd*duration;
                            }
                            kd_text=kd.toFixed(2) +' '+ ed;
                        }
                        base_form.findField('EvnCourseTreatDrug_MaxDoseDay').setValue(dd_text);
                        base_form.findField('EvnCourseTreatDrug_PrescrDose').setValue(kd_text);
						base_form.findField('EvnCourseTreatDrug_MinDoseDay').setValue(0+' '+ ed);
                        base_form.findField('EvnCourseTreatDrug_FactDose').setValue(0+' '+ ed);
                    }
                },
				xtype: 'numberfield'
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
						value: 1,
						minValue: 1,
						style: 'text-align: right;', 
						name: 'EvnCourseTreat_Duration',
						width: 100,
						tabIndex: TABINDEX_EVNPRESCR + 114,
						listeners: {
							change: function(field, newValue) {
                                var base_form = thas.FormPanel.getForm();
                                base_form.findField('EvnCourseTreat_ContReception').setValue(newValue);
                                var fieldCountInDay = base_form.findField('EvnCourseTreat_MaxCountDay');
                                fieldCountInDay.fireEvent('change', fieldCountInDay, fieldCountInDay.getValue());
							}
						},
						xtype: 'numberfield'
					},{
						allowDecimals: false,
						allowNegative: false,
						fieldLabel: lang['nepreryivnyiy_priem'],
						value: 1,
						minValue: 1,
						style: 'text-align: right;', 
						name: 'EvnCourseTreat_ContReception',
						width: 100,
						tabIndex: TABINDEX_EVNPRESCR + 116,
                        listeners: {
                            change: function() {
                                var base_form = thas.FormPanel.getForm();
                                var fieldCountInDay = base_form.findField('EvnCourseTreat_MaxCountDay');
                                fieldCountInDay.fireEvent('change', fieldCountInDay, fieldCountInDay.getValue());
                            }
                        },
						xtype: 'numberfield'
					},{
						allowDecimals: false,
						allowNegative: false,
						fieldLabel: lang['pereryiv'],
						value: 0,
						minValue: 0,
						style: 'text-align: right;', 
						name: 'EvnCourseTreat_Interval',
						width: 100,
						tabIndex: TABINDEX_EVNPRESCR + 118,
                        listeners: {
                            change: function() {
                                var base_form = thas.FormPanel.getForm();
                                var fieldCountInDay = base_form.findField('EvnCourseTreat_MaxCountDay');
                                fieldCountInDay.fireEvent('change', fieldCountInDay, fieldCountInDay.getValue());
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
							'change': function(combo, newValue, oldValue) {
								var base_form = thas.FormPanel.getForm();
								var record = combo.getStore().getById(newValue);
								if ( !record ) {
									return false;
								}
								base_form.findField('DurationType_recid').setValue(newValue);
								base_form.findField('DurationType_intid').setValue(newValue);
                                var fieldCountInDay = base_form.findField('EvnCourseTreat_MaxCountDay');
                                fieldCountInDay.fireEvent('change', fieldCountInDay, fieldCountInDay.getValue());
                                return true;
							}
						},
						xtype: 'swdurationtypecombo'
					},{
						hiddenName: 'DurationType_recid',//Тип Непрерывный прием
						width: 70,
						value: 1,
						tabIndex: TABINDEX_EVNPRESCR + 117,
                        listeners: {
                            change: function() {
                                var base_form = thas.FormPanel.getForm();
                                var fieldCountInDay = base_form.findField('EvnCourseTreat_MaxCountDay');
                                fieldCountInDay.fireEvent('change', fieldCountInDay, fieldCountInDay.getValue());
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
                                var base_form = thas.FormPanel.getForm();
                                var fieldCountInDay = base_form.findField('EvnCourseTreat_MaxCountDay');
                                fieldCountInDay.fireEvent('change', fieldCountInDay, fieldCountInDay.getValue());
                            }
                        },
						xtype: 'swdurationtypecombo'
					}]
				}]
            },{
                name: 'EvnCourseTreatDrug_MaxDoseDay',
                fieldLabel: lang['maksimalnaya_dnevnaya_doza'],
                disabled: true,
                width: 200,
                xtype: 'textfield'
            },{
                name: 'EvnCourseTreatDrug_PrescrDose',
                fieldLabel: lang['naznachennaya_kursovaya_doza'],
                disabled: true,
                width: 200,
                xtype: 'textfield'
            },{
                name: 'EvnCourseTreatDrug_MinDoseDay',
                fieldLabel: lang['minimalnaya_dnevnaya_doza'],
                disabled: true,
                width: 200,
                xtype: 'textfield'
            },{
                name: 'EvnCourseTreatDrug_FactDose',
                fieldLabel: lang['ispolnennaya_kursovaya_doza'],
                disabled: true,
                width: 200,
                xtype: 'textfield'
            },{
                comboSubject: 'PerformanceType',
                fieldLabel: lang['ispolnenie'],
                width: 370,
                tabIndex: TABINDEX_EVNPRESCR + 121,
                xtype: 'swcommonsprcombo'
			}, {
				boxLabel: 'Cito',
				checked: false,
				fieldLabel: '',
				labelSeparator: '',
				name: 'EvnPrescrTreat_IsCito',
				tabIndex: TABINDEX_EVNPRESCR + 122,
				xtype: 'checkbox'
			}, {
				fieldLabel: lang['kommentariy'],
				height: 70,
				name: 'EvnPrescrTreat_Descr',
				width: 370,
				tabIndex: TABINDEX_EVNPRESCR + 123,
				xtype: 'textarea'
			}]
		});

		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.doSave();
				}.createDelegate(this),
				iconCls: 'save16',
				tabIndex: TABINDEX_EVNPRESCR + 125,
				text: BTN_FRMSAVE
			}, {
				hidden: true,
                handler: function() {
					this.doSave({signature: true});
				}.createDelegate(this),
				iconCls: 'signature16',
				tabIndex: TABINDEX_EVNPRESCR + 126,
				text: BTN_FRMSIGN
			}, {
				text: '-'
			},
			//HelpButton(this, -1),
			{
				handler: function() {
					this.hide();
				}.createDelegate(this),
				iconCls: 'cancel16',
				onTabAction: function () {
					this.FormPanel.getForm().findField('MethodInputDrug_id').focus(true, 250);
				}.createDelegate(this),
				tabIndex: TABINDEX_EVNPRESCR + 129,
				text: BTN_FRMCANCEL
			}],
			items: [
				this.FormPanel
			],
			layout: 'form'
		});

		sw.Promed.swPolkaEvnPrescrTreatEditWindow.superclass.initComponent.apply(this, arguments);
	},
	keys: [{
		alt: true,
		fn: function(inp, e) {
			var current_window = Ext.getCmp('PolkaEvnPrescrTreatEditWindow');

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
		'beforehide': function(win) {
			//
		},
		'hide': function(win) {
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
		sw.Promed.swPolkaEvnPrescrTreatEditWindow.superclass.show.apply(this, arguments);

		this.center();

		var base_form = this.FormPanel.getForm();
		base_form.reset();
		this.DrugPanel.reset();

		this.parentEvnClass_SysNick = null;
		this.action = null;
		this.callback = Ext.emptyFn;
		this.formStatus = 'edit';
		this.onHide = Ext.emptyFn;
		
		if ( !arguments[0] || !arguments[0].formParams ) {
			sw.swMsg.alert(lang['soobschenie'], lang['nevernyie_parametryi'], function() { this.hide(); }.createDelegate(this) );
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

		if ( sw.Promed.MedStaffFactByUser.last ) {
			//чтобы выбирать с остатков отделения
			this.DrugPanel.LpuSection_id = sw.Promed.MedStaffFactByUser.last.LpuSection_id;
		}

		this.getLoadMask(LOAD_WAIT).show();

		switch ( this.action ) {
			case 'add':
				this.getLoadMask().hide();
				base_form.clearInvalid();
				this.setTitle(lang['naznachenie_lekarstvennogo_lecheniya_dobavlenie']);
				this.enableEdit(true);
				this.DrugPanel.onLoadForm();
				base_form.findField('MethodInputDrug_id').focus(true, 250);
			break;

			case 'addwithgrafcopy':
			case 'edit':
			case 'view':
				base_form.load({
					failure: function() {
						this.getLoadMask().hide();
						sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_zagruzke_dannyih_formyi'], function() { this.hide(); }.createDelegate(this) );
					}.createDelegate(this),
					params: {
						'EvnPrescrTreat_id': base_form.findField('EvnPrescrTreat_id').getValue(),
						'parentEvnClass_SysNick': this.parentEvnClass_SysNick
					},
					success: function(frm, act) {
						this.getLoadMask().hide();
						base_form.clearInvalid();
						if ( this.action == 'addwithgrafcopy' ) {
							this.setTitle(lang['naznachenie_lekarstvennogo_lecheniya_dobavlenie']);
							this.enableEdit(true);
							base_form.findField('accessType').setValue(null);
							base_form.findField('EvnPrescrTreat_id').setValue(null);
							base_form.findField('PrescriptionStatusType_id').setValue(1);
							base_form.findField('EvnPrescrTreatDrug_id').setValue(null);
							base_form.findField('MethodInputDrug_id').setValue(this.DrugPanel.defaultMethodInputDrug_id);
							base_form.findField('DrugComplexMnn_id').setValue(null);
							//base_form.findField('DrugPrepFas_id').setValue(null);
							base_form.findField('Drug_id').setValue(null);
							base_form.findField('EdUnits_id').setValue(null);
							base_form.findField('DrugForm_Name').setValue(null);
							this.findById('EPRTREF_DrugForm_Nick').setText('');
							base_form.findField('PrescriptionIntroType_id').setValue(null);
							base_form.findField('EvnPrescrTreatDrug_KolvoEd').setValue(null);
							//base_form.findField('EvnPrescrTreatDrug_Kolvo_Show').setValue(null);
							base_form.findField('EvnPrescrTreatDrug_Kolvo').setValue(null);
							base_form.findField('EvnPrescrTreat_IsCito').setValue('off');
							base_form.findField('EvnPrescrTreat_Descr').setValue('');
							base_form.findField('accessType').setValue(null);
							/*
							copy params:
							{ name: 'EvnPrescrTreat_setDate' },
							{ name: 'EvnCourseTreat_MaxCountDay' },
							{ name: 'EvnCourseTreat_Duration' },
							{ name: 'EvnCourseTreat_ContReception' },
							{ name: 'EvnCourseTreat_Interval' },
							{ name: 'DurationType_id' },
							{ name: 'DurationType_recid' },
							{ name: 'DurationType_intid' },
							{ name: 'PerformanceType_id' },
							{ name: 'PersonEvn_id' },
							{ name: 'Server_id' }
							*/
							this.DrugPanel.onLoadForm();
							base_form.findField('MethodInputDrug_id').focus(true, 250);
							return true;
						}
						if ( base_form.findField('accessType').getValue() == 'view' ) {
							this.action = 'view';
						}

						if ( this.action == 'edit' ) {
							this.setTitle(lang['naznachenie_lekarstvennogo_lecheniya_redaktirovanie']);
							this.enableEdit(true);
						}
						else {
							this.setTitle(lang['naznachenie_lekarstvennogo_lecheniya_prosmotr']);
							this.enableEdit(false);
						}
						this.DrugPanel.onLoadForm();
						this.setDrugForm();

						if ( this.action == 'edit' ) {
							base_form.findField('MethodInputDrug_id').focus(true, 250);
						}
						else {
							this.buttons[this.buttons.length - 1].focus();
						}
					}.createDelegate(this),
					url: '/?c=EvnPrescr&m=loadEvnPrescrTreatEditForm'
				});
			break;

			default:
				this.getLoadMask().hide();
				this.hide();
			break;
		}
	},
	width: 550
});