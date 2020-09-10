/**
* swPersonWeightEditWindow - форма редактирования массы человека
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Person
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Stas Bykov aka Savage (savage1981@gmail.com)
* @version      27.12.2010
* @comment      Префикс для id компонентов PWEF (PersonWeightEditForm)
*/

sw.Promed.swPersonWeightEditWindow = Ext.extend(sw.Promed.BaseForm, {
	action: null,
	autoHeight: true,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	collapsible: false,
	doSave: function(options) {
		// options @Object
		// options.ignoreWeightIsIncorrect @Boolean Признак игнорирования проверки правильности ввода массы

		if ( this.formStatus == 'save' ) {
			return false;
		}

		this.formStatus = 'save';

		var form = this.FormPanel;
		var base_form = form.getForm();

		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					this.formStatus = 'edit';
					form.getFirstInvalidEl().focus(false);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
        var _this = this;
        if(base_form.findField('PersonWeight_Weight').getValue() == 0){
            sw.swMsg.show({
                buttons: Ext.Msg.OK,
                fn: function() {
                    _this.formStatus = 'edit';
                },
                icon: Ext.Msg.WARNING,
                msg: lang['znachenie_pokazatelya_doljno_byit_otlichno_ot_nulya'],
                title: lang['oshibka_sohraneniya']
            });
            return false;
        }
        switch(base_form.findField('Okei_id').getValue()){
            case 36: //вес в граммах
                if(base_form.findField('PersonWeight_Weight').getValue() > 999999){
                    sw.swMsg.show({
                        buttons: Ext.Msg.OK,
                        fn: function() {
                            _this.formStatus = 'edit';
                        },
                        icon: Ext.Msg.WARNING,
                        msg: lang['vyi_vvodite_znachenie_vesa_v_grammah_znachenie_ne_doljno_byit_bolee_999_999'],
                        title: lang['oshibka_sohraneniya']
                    });
                    return false;
                }
                break;
            case 37: //вес в килограммах
                if(base_form.findField('PersonWeight_Weight').getValue() > 999.999){
                    sw.swMsg.show({
                        buttons: Ext.Msg.OK,
                        fn: function() {
                            _this.formStatus = 'edit';
                        },
                        icon: Ext.Msg.WARNING,
                        msg: lang['vyi_vvodite_znachenie_vesa_v_kilogrammah_znachenie_ne_doljno_byit_bolee_999_999'],
                        title: lang['oshibka_sohraneniya']
                    });
                    return false;
                }
                break;

        }

		// Если не задан признак игнорирования проверки правильности ввода массы...
		if ( this.personMode == 'child' && (!options || !options.ignoreWeightIsIncorrect) ) {
			var weight = base_form.findField('PersonWeight_Weight').getValue();
			var okei_id = base_form.findField('Okei_id').getValue();

			if (okei_id == 37) {
				weight = Number(weight) * 1000;
			} else {
				weight = Number(weight);
			}
			
			// ... и масса не в диапазоне 500-8000...
			if ( weight < 500 || weight > 8000 ) {
				// ... задаем вопрос
				sw.swMsg.show({
					buttons: Ext.Msg.YESNO,
					fn: function(buttonId, text, obj) {
						this.formStatus = 'edit';

						if ( buttonId == 'yes' ) {
							this.doSave({ ignoreWeightIsIncorrect: true });
						}
					}.createDelegate(this),
					icon: Ext.MessageBox.QUESTION,
					msg: lang['vozmojno_massa_vvedena_neverno_sohranit'],
					title: lang['vopros']
				});
				return false;
			}
		}

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Подождите, идет сохранение..." });
		loadMask.show();

		var weight_measure_type_code = 0;
		var weight_measure_type_id = base_form.findField('WeightMeasureType_id').getValue();
		var index;
		var params = new Object();

		index = base_form.findField('WeightMeasureType_id').getStore().findBy(function(rec) {
			if ( rec.get('WeightMeasureType_id') == weight_measure_type_id ) {
				return true;
			}
			else {
				return false;
			}
		});

		if ( index >= 0 ) {
			weight_measure_type_code = base_form.findField('WeightMeasureType_id').getStore().getAt(index).get('WeightMeasureType_Code');
		}

		var data = new Object();

		switch ( this.formMode ) {
			case 'local':
				var PersonWeight_text = base_form.findField('PersonWeight_Weight').getValue() + ' ' + base_form.findField('Okei_id').getFieldValue('Okei_NationSymbol');

				data.personWeightData = {
					'Evn_id': base_form.findField('Evn_id').getValue(),
					'PersonWeight_id': base_form.findField('PersonWeight_id').getValue(),
					'Person_id': base_form.findField('Person_id').getValue(),
					'PersonWeight_setDate': base_form.findField('PersonWeight_setDate').getValue(),
					'PersonWeight_Weight': base_form.findField('PersonWeight_Weight').getValue(),
					'PersonWeight_text': PersonWeight_text,
					'Okei_id': base_form.findField('Okei_id').getValue(),
					'PersonWeight_IsAbnorm': base_form.findField('PersonWeight_IsAbnorm').getValue(),
					'WeightAbnormType_id': base_form.findField('WeightAbnormType_id').getValue(),
					'WeightMeasureType_id': weight_measure_type_id,
					'WeightMeasureType_Code': weight_measure_type_code
				};

				this.callback(data);

				this.formStatus = 'edit';
				loadMask.hide();

				this.hide();
			break;

			case 'remote':
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
							if ( action.result.PersonWeight_id > 0 ) {
								base_form.findField('PersonWeight_id').setValue(action.result.PersonWeight_id);
								
								var PersonWeight_text = base_form.findField('PersonWeight_Weight').getValue() + ' ' + base_form.findField('Okei_id').getFieldValue('Okei_NationSymbol');
								
								data.personWeightData = {
									'Evn_id': base_form.findField('Evn_id').getValue(),
									'PersonWeight_id': base_form.findField('PersonWeight_id').getValue(),
									'Person_id': base_form.findField('Person_id').getValue(),
									'PersonWeight_setDate': base_form.findField('PersonWeight_setDate').getValue(),
									'PersonWeight_Weight': base_form.findField('PersonWeight_Weight').getValue(),
									'PersonWeight_text': PersonWeight_text,
									'Okei_id': base_form.findField('Okei_id').getValue(),
									'PersonWeight_IsAbnorm': base_form.findField('PersonWeight_IsAbnorm').getValue(),
									'WeightAbnormType_id': base_form.findField('WeightAbnormType_id').getValue(),
									'WeightMeasureType_id': weight_measure_type_id,
									'WeightMeasureType_Code': weight_measure_type_code
								};

								this.callback(data);
								this.hide();
							}
							else {
								if ( action.result.Error_Msg ) {
									sw.swMsg.alert(lang['oshibka'], action.result.Error_Msg);
								}
								else {
									sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_3]']);
								}
							}
						}
						else {
							sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_2]']);
						}
					}.createDelegate(this)
				});
			break;

			default:
				loadMask.hide();
			break;
		}
	},
	draggable: true,
	enableEdit: function(enable) {
		var base_form = this.FormPanel.getForm();
		var form_fields = new Array(
			'WeightMeasureType_id',
			'PersonWeight_Weight',
			'PersonWeight_IsAbnorm',
			'PersonWeight_setDate',
			'Okei_id'
		);
		var i = 0;

		for ( i = 0; i < form_fields.length; i++ ) {
			if ( enable ) {
				base_form.findField(form_fields[i]).enable();
			}
			else {
				base_form.findField(form_fields[i]).disable();
			}
		}

		if ( enable ) {
			this.buttons[0].show();
		}
		else {
			this.buttons[0].hide();
		}
	},
	formMode: 'remote',
	formStatus: 'edit',
	id: 'PersonWeightEditWindow',
	initComponent: function() {
		this.FormPanel = new Ext.form.FormPanel({
			autoHeight: true,
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			border: false,
			frame: true,
			id: 'PersonWeightEditForm',
			labelAlign: 'right',
			labelWidth: 130,
			reader: new Ext.data.JsonReader({
				success: Ext.amptyFn
			},  [
				{ name: 'Evn_id' },
				{ name: 'WeightAbnormType_id' },
				{ name: 'WeightMeasureType_id' },
				{ name: 'PersonWeight_id' },
				{ name: 'Okei_id' },
				{ name: 'PersonWeight_IsAbnorm' },
				{ name: 'PersonWeight_Weight' },
				{ name: 'PersonWeight_setDate' },
				{ name: 'Person_id' },
				{ name: 'Server_id' }
			]),
			url: '/?c=PersonWeight&m=savePersonWeight',

			items: [{
				name: 'PersonWeight_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'Person_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'Evn_id',
				xtype: 'hidden'
			}, {
				name: 'Server_id',
				value: -1,
				xtype: 'hidden'
			}, {
				allowBlank: false,
				fieldLabel: lang['data_izmereniya'],
				format: 'd.m.Y',
				name: 'PersonWeight_setDate',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				selectOnFocus: true,
				tabIndex: TABINDEX_PWEF + 1,
				width: 100,
				xtype: 'swdatefield'
			}, {
				allowBlank: false,
				autoLoad: false,
				comboSubject: 'WeightMeasureType',
				fieldLabel: lang['vid_zamera'],
				hiddenName: 'WeightMeasureType_id',
				lastQuery: '',
				listeners: {
					'render': function(combo) {
						combo.getStore().load();
					}
				},
				tabIndex: TABINDEX_PWEF + 2,
				width: 350,
				xtype: 'swcommonsprcombo'
			}, {
				border: false,
				layout: 'column',
				items: [{
					border: false,
					layout: 'form',
					items: [{
						allowBlank: false,
						allowNegative: false,
						decimalPrecision: 0,
						fieldLabel: lang['massa'],
						name: 'PersonWeight_Weight',
						tabIndex: TABINDEX_PWEF + 3,
						regex:new RegExp('(^[0-9]{0,6})$'),
						maxValue:999999,
						width: 100,
                        maxLength: 6,
						xtype: 'numberfield',
                        maxLengthText: lang['maksimalnaya_dlina_etogo_polya_6_simvolov']
					}]
				}, {
					border: false,
					layout: 'form',
					items: [{
						hideLabel: true,
						allowBlank: false,
						width: 60,
						value: 36,
						name: 'Okei_id',
						tabIndex: TABINDEX_PWEF + 4,
						loadParams: {params: {where: ' where Okei_id in (36,37)'}},
                        listeners: {
                            'select': function(v,r,id) {
                                var base_form = this.FormPanel.getForm();
                                if(id == 0){
                                    base_form.findField('PersonWeight_Weight').setValue('');
                                      base_form.findField('PersonWeight_Weight').regex=new RegExp('[0-9]{0,6}');
										base_form.findField('PersonWeight_Weight').maxValue=999999;
                                        base_form.findField('PersonWeight_Weight').maxLength = 6;
                                        base_form.findField('PersonWeight_Weight').maxLengthText = lang['maksimalnaya_dlina_etogo_polya_3_simvolov'];
                                        base_form.findField('PersonWeight_Weight').decimalPrecision = 0;
                                }
                                else{
									log(base_form.findField('PersonWeight_Weight'));
									base_form.findField('PersonWeight_Weight').setValue('');
									base_form.findField('PersonWeight_Weight').regex=new RegExp('(^[0-9]{1,3}\.[0-9]{0,3})$');
									base_form.findField('PersonWeight_Weight').maxValue=999.999;
									base_form.findField('PersonWeight_Weight').maxLength = 7;
									base_form.findField('PersonWeight_Weight').maxLengthText = lang['maksimalnaya_dlina_etogo_polya_6_simvolov_bez_ucheta_znaka_razdelitelya_v_drobnyih_chislah'];
									base_form.findField('PersonWeight_Weight').decimalPrecision = 3;
                                }
                            }.createDelegate(this)
                        },
						xtype: 'swokeicombo'
					}]
				}]
			}, {
				allowBlank: true,
				comboSubject: 'YesNo',
				fieldLabel: lang['otklonenie'],
				hiddenName: 'PersonWeight_IsAbnorm',
				listeners: {
					'change': function(combo, newValue, oldValue) {
						var base_form = this.FormPanel.getForm();
						var record = combo.getStore().getById(newValue);

						if ( record && record.get('YesNo_Code') == 1 ) {
							base_form.findField('WeightAbnormType_id').enable();
						}
						else {
							base_form.findField('WeightAbnormType_id').clearValue();
							base_form.findField('WeightAbnormType_id').disable();
						}
					}.createDelegate(this)
				},
				tabIndex: TABINDEX_PWEF + 5,
				width: 100,
				xtype: 'swcommonsprcombo'
			}, {
				allowBlank: true,
				comboSubject: 'WeightAbnormType',
				fieldLabel: lang['tip'],
				hiddenName: 'WeightAbnormType_id',
				tabIndex: TABINDEX_PWEF + 6,
				width: 200,
				xtype: 'swcommonsprcombo'
			}]
		});

		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.doSave();
				}.createDelegate(this),
				iconCls: 'save16',
				onShiftTabAction: function () {
					var base_form = this.FormPanel.getForm();

					if ( this.action == 'view' ) {
						this.buttons[this.buttons.length - 1].focus(true);
					}
					else if ( !base_form.findField('WeightAbnormType_id').disabled ) {
						base_form.findField('WeightAbnormType_id').focus();
					}
					else {
						base_form.findField('PersonWeight_IsAbnorm').focus();
					}
				}.createDelegate(this),
				onTabAction: function () {
					this.buttons[this.buttons.length - 1].focus(true);
				}.createDelegate(this),
				tabIndex: TABINDEX_PWEF + 7,
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
					if ( !this.buttons[0].hidden ) {
						this.buttons[0].focus(true);
					}
				}.createDelegate(this),
				onTabAction: function () {
					if ( !this.FormPanel.getForm().findField('PersonWeight_setDate').disabled ) {
						this.FormPanel.getForm().findField('PersonWeight_setDate').focus(true);
					}
					else if ( !this.buttons[0].hidden ) {
						this.buttons[0].focus(true);
					}
				}.createDelegate(this),
				tabIndex: TABINDEX_PWEF + 8,
				text: BTN_FRMCANCEL
			}],
			items: [
				this.FormPanel
			],
			layout: 'form'
		});

		sw.Promed.swPersonWeightEditWindow.superclass.initComponent.apply(this, arguments);
	},
	keys: [{
		alt: true,
		fn: function(inp, e) {
			var current_window = Ext.getCmp('PersonWeightEditWindow');

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
		stopEvent: true
	}],
	listeners: {
		'beforehide': function(win) {
			// 
		},
		'hide': function(win) {
			win.onHide();
		}
	},
	maximizable: false,
	maximized: false,
	modal: true,
	onHide: Ext.emptyFn,
	plain: true,
	resizable: false,
	show: function() {
		sw.Promed.swPersonWeightEditWindow.superclass.show.apply(this, arguments);

		this.center();

		var base_form = this.FormPanel.getForm();
		base_form.reset();

		this.action = null;
		this.callback = Ext.emptyFn;
		this.formMode = 'remote';
		this.formStatus = 'edit';
		this.measureTypeExceptions = new Array();
		this.onHide = Ext.emptyFn;
		this.personMode = 'man';

		if ( !arguments[0] || !arguments[0].formParams ) {
			sw.swMsg.alert(lang['soobschenie'], lang['nevernyie_parametryi'], function() { this.hide(); }.createDelegate(this) );
			return false;
		}

		base_form.findField('WeightMeasureType_id').getStore().clearFilter();
		base_form.findField('WeightMeasureType_id').lastQuery = '';

		base_form.setValues(arguments[0].formParams);

		if ( arguments[0].action ) {
			this.action = arguments[0].action;
		}

		if ( arguments[0].callback ) {
			this.callback = arguments[0].callback;
		}
		
		if ( arguments[0].formMode && typeof arguments[0].formMode == 'string' && arguments[0].formMode.inlist([ 'local', 'remote' ]) ) {
			this.formMode = arguments[0].formMode;
		}

		if ( arguments[0].measureTypeExceptions && typeof arguments[0].measureTypeExceptions == 'object' ) {
			this.measureTypeExceptions = arguments[0].measureTypeExceptions;
		}
		
		if ( arguments[0].onHide ) {
			this.onHide = arguments[0].onHide;
		}

		if ( arguments[0].personMode && typeof arguments[0].personMode == 'string' && arguments[0].personMode.inlist([ 'child', 'man' ]) ) {
			this.personMode = arguments[0].personMode;
		}

		if ( arguments[0].Okei_InterNationSymbol && arguments[0].Okei_InterNationSymbol == 'kg' ) {
			var store = base_form.findField('Okei_id').getStore(),
				indexStore = store.findBy(function(rec) { return (rec.get('Okei_InterNationSymbol') == 'kg'); }),
				rec = store.getAt(indexStore),
				Okei_id = rec.get('Okei_id');
			if(Okei_id)
				base_form.findField('Okei_id').setValue(Okei_id);
		}

		base_form.findField('WeightAbnormType_id').disable();

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT });
		loadMask.show();

		var index;
		var record;

		switch ( this.action ) {
			case 'add':
				this.setTitle(WND_PERSWEIGHT_ADD);
				this.enableEdit(true);

				if ( this.measureTypeExceptions.length > 0 ) {
					base_form.findField('WeightMeasureType_id').getStore().filterBy(function(rec) {
						if ( rec.get('WeightMeasureType_Code').toString().inlist(this.measureTypeExceptions) ) {
							return false;
						}
						else {
							return true;
						}
					}.createDelegate(this));

					if ( base_form.findField('WeightMeasureType_id').getStore().getCount() == 1 ) {
						base_form.findField('WeightMeasureType_id').setValue(base_form.findField('WeightMeasureType_id').getStore().getAt(0).get('WeightMeasureType_id'));
					}
				}

				base_form.findField('PersonWeight_IsAbnorm').setValue(1);

				loadMask.hide();

				//если передали дату - устанавливаю ее.
				if ( (arguments[0].PersonWeight_setDate != undefined) && (arguments[0].PersonWeight_setDate != null)) {
					base_form.findField('PersonWeight_setDate').setValue(arguments[0].PersonWeight_setDate);
				} else {
					//если не передали - устанавливаю текущую
					setCurrentDateTime({
						callback: function() {
							base_form.findField('PersonWeight_setDate').focus(true, 250);
						}.createDelegate(this),
						dateField: base_form.findField('PersonWeight_setDate'),
						loadMask: true,
						setDate: true,
						setDateMaxValue: true,
						windowId: this.id
					});
				}
			break;

			case 'edit':
			case 'view':
				if ( this.formMode == 'local' ) {
					if ( this.action == 'edit' ) {
						this.setTitle(WND_PERSWEIGHT_EDIT);
						this.enableEdit(true);
					}
					else {
						this.setTitle(WND_PERSWEIGHT_VIEW);
						this.enableEdit(false);
					}

					if ( this.action == 'edit' ) {
						setCurrentDateTime({
							dateField: base_form.findField('PersonWeight_setDate'),
							loadMask: true,
							setDate: false,
							setDateMaxValue: true,
							windowId: this.id
						});

						var record = base_form.findField('PersonWeight_IsAbnorm').getStore().getById(base_form.findField('PersonWeight_IsAbnorm').getValue());

						if ( record && record.get('YesNo_Code') == 1 ) {
							base_form.findField('WeightAbnormType_id').enable();
						}
						else {
							base_form.findField('WeightAbnormType_id').clearValue();
							base_form.findField('WeightAbnormType_id').disable();
						}

						if ( this.measureTypeExceptions.length > 0 ) {
							base_form.findField('WeightMeasureType_id').getStore().filterBy(function(rec) {
								if ( rec.get('WeightMeasureType_Code').toString().inlist(this.measureTypeExceptions) && rec.get('WeightMeasureType_id') != base_form.findField('WeightMeasureType_id').getValue() ) {
									return false;
								}
								else {
									return true;
								}
							}.createDelegate(this));
						}
					}

					loadMask.hide();

					if ( this.action == 'edit' ) {
						base_form.findField('PersonWeight_setDate').focus(true, 250);
					}
					else {
						this.buttons[this.buttons.length - 1].focus();
					}
				}
				else {
					var person_weight_id = base_form.findField('PersonWeight_id').getValue();

					if ( !person_weight_id ) {
						loadMask.hide();
						this.hide();
						return false;
					}

					base_form.load({
						failure: function() {
							loadMask.hide();
							sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_zagruzke_dannyih_formyi'], function() { this.hide(); }.createDelegate(this) );
						}.createDelegate(this),
						params: {
							'PersonWeight_id': person_weight_id
						},
						success: function() {
							if ( this.action == 'edit' ) {
								this.setTitle(WND_PERSWEIGHT_EDIT);
								this.enableEdit(true);
							}
							else {
								this.setTitle(WND_PERSWEIGHT_VIEW);
								this.enableEdit(false);
							}

							loadMask.hide();

							if ( this.action == 'view' ) {
								this.buttons[this.buttons.length - 1].focus();
							}
							else {
								base_form.findField('PersonWeight_setDate').focus(true, 250);
							}
						}.createDelegate(this),
						url: '/?c=PersonWeight&m=loadPersonWeightEditForm'
					});
				}
			break;

			default:
				loadMask.hide();
				this.hide();
			break;
		}
	},
	width: 600
});