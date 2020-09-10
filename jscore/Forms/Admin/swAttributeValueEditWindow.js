/**
 * swAttributeValueEditWindow - окно редактирования значения
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Admin
 * @access			public
 * @copyright		Copyright (c) 2015 Swan Ltd.
 * @author			Dmitriy Vlasenko
 * @version			25.01.2015
 */

/*NO PARSE JSON*/

sw.Promed.swAttributeValueEditWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'swAttributeValueEditWindow',
	width: 640,
	autoHeight: true,
	modal: true,

	fieldNames: [],
	formStatus: 'edit',
	action: 'view',
	callback: Ext.emptyFn,

	doSave: function() {
		if (this.formStatus == 'save') {
			return false;
		}
		this.formStatus = 'save';

		var
			base_form = this.FormPanel.getForm(),
			i,
			params = new Object(),
			win = this;

		if (!base_form.isValid()) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function()
				{
					log(this);
					this.formStatus = 'edit';
					this.FormPanel.getFirstInvalidEl().focus(true);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT_SAVE });
		loadMask.show();

		params = {
			AttributeVision_TableName: win.AttributeVision_TableName,
			AttributeVision_TablePKey: win.AttributeVision_TablePKey,
			AttributeValue_id: win.AttributeValue_id
		}

		// Получаем значения задисабленных полей
		for ( i = 0; i < win.fieldNames.length; i++) {
			if ( win.findById(win.id + '_' + win.fieldNames[i]).disabled ) {
				params[win.fieldNames[i]] = win.findById(win.id + '_' + win.fieldNames[i]).getValue();
			}
		}

		base_form.submit({
			params: params,
			failure: function(result_form, action) {
				this.formStatus = 'edit';
				loadMask.hide();
			}.createDelegate(this),
			success: function(result_form, action) {
				loadMask.hide();
				if (typeof this.callback == 'function') {
					this.callback();
				}
				this.formStatus = 'edit';
				this.hide();
			}.createDelegate(this)
		});
	},
	setFormFields: function(fields) {
		var win = this;
		var contId = this.id + '_Container',
			items = [],
			maxWidthLabel = 0;

		var MesCodeName = null;
		var LpuComboName = null;
		var LpuSectionComboName = null;
		var LpuSectionProfileComboName = null;
		var MedStaffFactComboName = null;
		var UslugaComplexComboName = null;
		var keyName, formReaderFields = new Array();

		// Получаем название поля первичного ключа
		for(var i=0; i<fields.length; i++) {
			if ( fields[i].name == 'keyName' ) {
				keyName = fields[i].value;
			}
			else {
				win.fieldNames.push(fields[i].name);
			}
		}

		for(var i=0; i<fields.length; i++) {
			if( fields[i].fieldLabel ) {
				maxWidthLabel = maxWidthLabel < fields[i].fieldLabel.length ? fields[i].fieldLabel.length : maxWidthLabel;
			}
			formReaderFields.push({ name: fields[i].name });
			//для поля "идентификатор" добавляю дизабленый контрол со значением, чисто для отображения
			if (keyName == fields[i].name) {
				var fieldLabel;
				if (fields[i].fieldLabel) {
					fieldLabel = fields[i].fieldLabel;
				} else {
					fieldLabel = lang['identifikator'];
				}
				maxWidthLabel = maxWidthLabel < fieldLabel ? fieldLabel : maxWidthLabel;
				items.push({
					readOnly: true,
					name: fields[i].name,
					id: win.id + '_' + fields[i].name,
					fieldLabel: fieldLabel,
					value: fields[i].value,
					xtype: "textfield"
				});
			} else {
				if (fields[i].isOtkazVolume) {
					if (fields[i].name == 'atrib_112') {
						MesCodeName = fields[i].name;
						UslugaComplexComboName = fields[i].UslugaComplexComboName;
						fields[i].listeners = {
							'change': function(field, newValue, oldValue) {
								// прогрузить список услуг
								var uslugaComplexCombo = win.findById(win.id + '_' + UslugaComplexComboName);
								var UslugaComplex_id = uslugaComplexCombo.getValue();
								uslugaComplexCombo.clearValue();
								uslugaComplexCombo.getStore().removeAll();
								if (newValue) {
									uslugaComplexCombo.getStore().load({
										params: {
											Mes_Code: newValue,
											requiredOnly: 1
										},
										callback: function() {
											var index = uslugaComplexCombo.getStore().findBy(function (rec) {
												return (rec.get('UslugaComplex_id') == UslugaComplex_id);
											});
											var record = uslugaComplexCombo.getStore().getAt(index);
											if (record) {
												uslugaComplexCombo.setValue(record.get('UslugaComplex_id'))
											}
											uslugaComplexCombo.fireEvent('change', uslugaComplexCombo, uslugaComplexCombo.getValue());
										}
									});
								}
							}
						};
					}
					else if (fields[i].name == 'atrib_105') {
						UslugaComplexComboName = fields[i].UslugaComplexComboName;
						fields[i].listeners = {
							'change': function(field, newValue, oldValue) {
								// прогрузить список услуг
								var uslugaComplexCombo = win.findById(win.id + '_' + UslugaComplexComboName);
								var UslugaComplex_id = uslugaComplexCombo.getValue();
								uslugaComplexCombo.clearValue();
								uslugaComplexCombo.lastQuery = 'Avadakedavra';
								uslugaComplexCombo.getStore().removeAll();
								uslugaComplexCombo.getStore().baseParams.SurveyType_id = newValue;
								if (newValue) {
									uslugaComplexCombo.getStore().load({
										callback: function() {
											var index = uslugaComplexCombo.getStore().findBy(function (rec) {
												return (rec.get('UslugaComplex_id') == UslugaComplex_id);
											});
											var record = uslugaComplexCombo.getStore().getAt(index);
											if (record) {
												uslugaComplexCombo.setValue(record.get('UslugaComplex_id'))
											}
											uslugaComplexCombo.fireEvent('change', uslugaComplexCombo, uslugaComplexCombo.getValue());
										}
									});
								}
							}
						};
					}
					else if (fields[i].xtype == 'swlpucombo') {
						LpuComboName = fields[i].name;
						LpuSectionComboName = fields[i].LpuSectionComboName;
						fields[i].listeners = {
							'change': function(combo, newValue, oldValue) {
								// прогрузить отделение
								var lpuSectionCombo = win.findById(win.id + '_' + LpuSectionComboName);
								var LpuSection_id = lpuSectionCombo.getValue();
								lpuSectionCombo.clearValue();
								lpuSectionCombo.getStore().removeAll();
								if (newValue) {
									lpuSectionCombo.getStore().load({
										params: {
											Lpu_id: newValue,
											mode: 'combo'
										},
										callback: function() {
											var index = lpuSectionCombo.getStore().findBy(function (rec) {
												if (rec.get('LpuSection_id') == LpuSection_id) {
													return true;
												}
												else {
													return false;
												}
											});
											var record = lpuSectionCombo.getStore().getAt(index);
											if (record) {
												lpuSectionCombo.setValue(record.get('LpuSection_id'))
											}
											lpuSectionCombo.fireEvent('change', lpuSectionCombo, lpuSectionCombo.getValue());
										}
									});
								}
							}
						};
					} else if (fields[i].xtype == 'swlpusectioncombo') {
						LpuSectionProfileComboName = fields[i].LpuSectionProfileComboName;
						MedStaffFactComboName = fields[i].MedStaffFactComboName;
						fields[i].listeners = {
							'change': function(combo, newValue, oldValue) {
								// прогрузить профиля и врачей
								var medStaffFactCombo = win.findById(win.id + '_' + MedStaffFactComboName);
								var MedStaffFact_id = medStaffFactCombo.getValue();
								medStaffFactCombo.clearValue();
								medStaffFactCombo.getStore().removeAll();
								if (newValue) {
									medStaffFactCombo.getStore().load({
										params: {
											LpuSection_id: newValue,
											mode: 'combo'
										},
										callback: function() {
											var index = medStaffFactCombo.getStore().findBy(function (rec) {
												if (rec.get('MedStaffFact_id') == MedStaffFact_id) {
													return true;
												}
												else {
													return false;
												}
											});
											var record = medStaffFactCombo.getStore().getAt(index);
											if (record) {
												medStaffFactCombo.setValue(record.get('MedStaffFact_id'))
											}
											medStaffFactCombo.fireEvent('change', medStaffFactCombo, medStaffFactCombo.getValue());
										}
									});
								}

								var lpuSectionProfileCombo = win.findById(win.id + '_' + LpuSectionProfileComboName);
								var LpuSectionProfile_id = lpuSectionProfileCombo.getValue();
								lpuSectionProfileCombo.clearValue();
								lpuSectionProfileCombo.getStore().removeAll();
								if (newValue) {
									lpuSectionProfileCombo.getStore().load({
										params: {
											LpuSection_id: newValue
										},
										callback: function() {
											var index = lpuSectionProfileCombo.getStore().findBy(function (rec) {
												if (rec.get('LpuSectionProfile_id') == LpuSectionProfile_id) {
													return true;
												}
												else {
													return false;
												}
											});
											var record = lpuSectionProfileCombo.getStore().getAt(index);
											if (record) {
												lpuSectionProfileCombo.setValue(record.get('LpuSectionProfile_id'))
											}
											lpuSectionProfileCombo.fireEvent('change', lpuSectionProfileCombo, lpuSectionProfileCombo.getValue());
										}
									});
								}
							}
						};
					}
				}
				else if (fields[i].isPerehodVolume) {
					if (fields[i].xtype == 'swlpucombo') {
						LpuComboName = fields[i].name;
						LpuSectionComboName = fields[i].LpuSectionComboName;
						fields[i].listeners = {
							'change': function(combo, newValue, oldValue) {
								// прогрузить отделение
								var lpuSectionCombo = win.findById(win.id + '_' + LpuSectionComboName);
								var LpuSection_id = lpuSectionCombo.getValue();
								lpuSectionCombo.clearValue();
								lpuSectionCombo.getStore().removeAll();
								if (newValue) {
									lpuSectionCombo.getStore().load({
										params: {
											Lpu_id: newValue,
											mode: 'combo'
										},
										callback: function() {
											lpuSectionCombo.lastQuery = '';
											lpuSectionCombo.getStore().filterBy(function(rec) {
												return rec.get('LpuUnitType_SysNick').inlist([ 'stac', 'dstac', 'hstac', 'pstac', 'priem' ]);
											});
											var index = lpuSectionCombo.getStore().findBy(function (rec) {
												return (rec.get('LpuSection_id') == LpuSection_id);
											});
											if (index >= 0) {
												lpuSectionCombo.setValue(LpuSection_id)
											}
										}
									});
								}
							}
						};
					}
				}

				if (fields[i].name == 'atrib_13' && 
					(win.AttributeVision_TablePKey.inlist([637, 638])) 
					&& getRegionNick() == 'khak'
				) {
					fields[i].decimalPrecision = 7;
				}

				if ( fields[i].name == 'AttributeValue_begDate' ) {
					fields[i].listeners = {
						'change': function(field, newValue, oldValue) {
							if ( !Ext.isEmpty(field.UslugaComplexComboName) ) {
								// прогрузить список услуг
								var
									uslugaComplexCombo = win.findById(win.id + '_' + field.UslugaComplexComboName),
									UslugaComplex_id = uslugaComplexCombo.getValue();

								uslugaComplexCombo.clearValue();
								uslugaComplexCombo.lastQuery = 'Avadakedavra';
								uslugaComplexCombo.getStore().removeAll();
								uslugaComplexCombo.getStore().baseParams.UslugaComplex_Date = Ext.util.Format.date(newValue, 'd.m.Y');
								if (newValue) {
									uslugaComplexCombo.getStore().load({
										callback: function() {
											var index = uslugaComplexCombo.getStore().findBy(function (rec) {
												return (rec.get('UslugaComplex_id') == UslugaComplex_id);
											});
											var record = uslugaComplexCombo.getStore().getAt(index);
											if (record) {
												uslugaComplexCombo.setValue(record.get('UslugaComplex_id'))
											}
											uslugaComplexCombo.fireEvent('change', uslugaComplexCombo, uslugaComplexCombo.getValue());
										}
									});
								}
							}

							if (getRegionNick() == 'vologda')
								win._checkDatePeriod();

							win.applyDateFilter();
						}
					};
				}
				if (fields[i].fieldLabel == 'МО' && this.lpu_id) {
					fields[i].disabled = true;
					fields[i].value = this.lpu_id;
				}

				if ( fields[i].name == 'AttributeValue_endDate' ) {
					fields[i].listeners = {
						'change': function(field, newValue, oldValue) {
							if (getRegionNick() == 'vologda')
								win._checkDatePeriod();

							win.applyDateFilter();
						}
					};
				}

				if ( fields[i].AgeGroupDispComboName) {
					var AgeGroupDispComboName = fields[i].AgeGroupDispComboName;
					fields[i].listeners = {
						'change': function(field, newValue, oldValue) {

							var DispType_id="";

							if(field.isOrp13ProfTeen){
								DispType_id = newValue == 10 ? 4 : 2;
							}
							
							if ( !Ext.isEmpty(AgeGroupDispComboName)){
								var params = {
									name: 'AgeGroupDisp',
									atributid: AgeGroupDispComboName,
									DispType_id: DispType_id
								};

								win.applyFilterCombo(params);
							}
							
						}
					};
				}

				fields[i].disabled = this.action === 'view' || fields[i].disabled;
				if (fields[i].name) {
					fields[i].id = win.id + '_' + fields[i].name;
				}
				items.push(fields[i]);
			}
		}

		var container = new sw.Promed.Panel({
			layout: 'form',
			autoHeight: true,
			labelAlign: 'right',
			labelWidth: maxWidthLabel*7, // TO-DO: надо по-другому считать
			id: contId,
			defaults: {
				anchor: '-10'
			},
			items: items
		});

		this.FormPanel = new Ext.form.FormPanel({
			bodyBorder: false,
			border: false,
			buttonAlign: 'left',
			frame: true,
			url: '/?c=TariffVolumes&m=saveAttributeValue',
			labelWidth: 150,
			labelAlign: 'right',
			reader: new Ext.data.JsonReader(
				{ success: Ext.emptyFn() },
				formReaderFields
			),
			items: [
				container
			]
		});

		this.mainPanel.add(this.FormPanel);
		this.mainPanel.doLayout();
		this.doLayout();

		this.center();

		if (LpuComboName) {
			var lpuCombo = win.findById(win.id + '_' + LpuComboName);
			lpuCombo.fireEvent('change', lpuCombo, lpuCombo.getValue());
		}

		if (MesCodeName) {
			var mesCodeField = win.findById(win.id + '_' + MesCodeName);
			mesCodeField.fireEvent('change', mesCodeField, mesCodeField.getValue());
		}
	},
	deleteFormFields: function() {
		this.mainPanel.removeAll(true);
		this.fieldNames = [];
	},
	applyDateFilter: function() {
		var base_form = this.FormPanel.getForm();
		var attrBegDate_isEmpty = false;
		var
			AttributeValue_begDate = base_form.findField('AttributeValue_begDate').getValue(),
			AttributeValue_endDate = base_form.findField('AttributeValue_endDate').getValue();

		if ( Ext.isEmpty(AttributeValue_begDate) ) {
			AttributeValue_begDate = getValidDT(getGlobalOptions().date, '');
			attrBegDate_isEmpty = true;
		}

		if ( typeof base_form.findField('atrib_187') == 'object' ) {
			base_form.findField('atrib_187').lastQuery = '';
			base_form.findField('atrib_187').getStore().clearFilter();
			base_form.findField('atrib_187').getStore().filterBy(function(rec) {
				return (
					(
						Ext.isEmpty(AttributeValue_begDate)
						|| Ext.isEmpty(rec.get('HTMedicalCareClass_endDate'))
						|| AttributeValue_begDate <= rec.get('HTMedicalCareClass_endDate')
					)
					&& (
						Ext.isEmpty(AttributeValue_endDate)
						|| Ext.isEmpty(rec.get('HTMedicalCareClass_begDate'))
						|| rec.get('HTMedicalCareClass_begDate') <= AttributeValue_endDate
					)
				);
			});
		}

		if ( typeof base_form.findField('atrib_26') == 'object' ) {
			base_form.findField('atrib_26').lastQuery = '';
			base_form.findField('atrib_26').getStore().clearFilter();
			base_form.findField('atrib_26').getStore().filterBy(function(rec) {
				return (
					(
						Ext.isEmpty(AttributeValue_begDate)
						|| Ext.isEmpty(rec.get('HTMedicalCareClass_endDate'))
						|| AttributeValue_begDate <= rec.get('HTMedicalCareClass_endDate')
					)
					&& (
						Ext.isEmpty(AttributeValue_endDate)
						|| Ext.isEmpty(rec.get('HTMedicalCareClass_begDate'))
						|| rec.get('HTMedicalCareClass_begDate') <= AttributeValue_endDate
					)
				);
			});
		}

		if ( typeof base_form.findField('atrib_217') == 'object' ) {
			base_form.findField('atrib_217').lastQuery = '';
			base_form.findField('atrib_217').getStore().clearFilter();
			base_form.findField('atrib_217').getStore().filterBy(function(rec) {
				var LpuSectionCode_begDT = null;
				var LpuSectionCode_endDT = null;
				if (rec.get('LpuSectionCode_begDT')) {
					LpuSectionCode_begDT = new Date(rec.get('LpuSectionCode_begDT').replace(/(\d{2})\.(\d{2})\.(\d{4})/, '$3-$2-$1'));
				}
				if (rec.get('LpuSectionCode_endDT')) {
					LpuSectionCode_endDT = new Date(rec.get('LpuSectionCode_endDT').replace(/(\d{2})\.(\d{2})\.(\d{4})/, '$3-$2-$1'));
				}
				return (
					(
						Ext.isEmpty(AttributeValue_begDate) || attrBegDate_isEmpty
						|| Ext.isEmpty(LpuSectionCode_endDT)
						|| Ext.isEmpty(rec.get('LpuSectionCode_endDT'))
						|| AttributeValue_begDate.getTime() <= LpuSectionCode_endDT.getTime()
					)
					&& (
						Ext.isEmpty(AttributeValue_endDate) || Ext.isEmpty(LpuSectionCode_endDT)
						|| Ext.isEmpty(rec.get('LpuSectionCode_begDT'))
						|| LpuSectionCode_endDT.getTime() <= AttributeValue_endDate.getTime()
					)
				);
			});
		}

		if ( typeof base_form.findField('atrib_191') == 'object' ) {
			base_form.findField('atrib_191').lastQuery = '';
			base_form.findField('atrib_191').getStore().clearFilter();
			base_form.findField('atrib_191').getStore().filterBy(function(rec) {
				return (
					(
						Ext.isEmpty(AttributeValue_begDate)
						|| Ext.isEmpty(rec.get('DrugTherapyScheme_endDate'))
						|| AttributeValue_begDate <= rec.get('DrugTherapyScheme_endDate')
					)
					&& (
						Ext.isEmpty(AttributeValue_endDate)
						|| Ext.isEmpty(rec.get('DrugTherapyScheme_begDate'))
						|| rec.get('DrugTherapyScheme_begDate') <= AttributeValue_endDate
					)
				);
			});
		}
		
	},
	applyFilterCombo: function (params) {
		var win = this,
			base_form = this.FormPanel.getForm(),
			AttributeValue_begDate = base_form.findField('AttributeValue_begDate').getValue(),
			AttributeValue_endDate = base_form.findField('AttributeValue_endDate').getValue();

		if ( Ext.isEmpty(AttributeValue_begDate) ) {
			AttributeValue_begDate = getValidDT(getGlobalOptions().date, '');
		}

		var Combo = win.findById(win.id + '_' + params.atributid);
		if(params.name=='AgeGroupDisp' && Combo){
			Combo.lastQuery ='';
			Combo.getStore().filterBy(function(rec) {
				if( 
					(
						Ext.isEmpty(AttributeValue_begDate)
						|| Ext.isEmpty(rec.get('AgeGroupDisp_endDate'))
						|| AttributeValue_begDate <= rec.get('AgeGroupDisp_endDate')
					)
					&& (
						Ext.isEmpty(AttributeValue_endDate)
						|| Ext.isEmpty(rec.get('AgeGroupDisp_begDate'))
						|| rec.get('AgeGroupDisp_begDate') <= AttributeValue_endDate
					)
				){
					if( params.DispType_id && rec.get('DispType_id') == params.DispType_id){
						return true;
					}else if(!params.DispType_id){
						return true;
					}
				};
			});
		}
	},
	show: function() {
		sw.Promed.swAttributeValueEditWindow.superclass.show.apply(this, arguments);

		this.deleteFormFields();
		this.syncShadow();

		var win = this;

		if (arguments[0].action) {
			this.action = arguments[0].action;
		}

		if (arguments[0].callback) {
			this.callback = arguments[0].callback;
		}

		if (arguments[0].lpu_id) {
			this.lpu_id = arguments[0].lpu_id;
		}

		if (!Ext.isEmpty(arguments[0].AIS)) {
			this.AIS = true;
		}

		this.AttributeVision_TableName = null;
		this.AttributeVision_TablePKey = null;
		this.AttributeValue_id = null;
		if (arguments[0].AttributeVision_TableName) {
			this.AttributeVision_TableName = arguments[0].AttributeVision_TableName;
		}
		if (arguments[0].AttributeVision_TablePKey) {
			this.AttributeVision_TablePKey = arguments[0].AttributeVision_TablePKey;
		}
		if (arguments[0].AttributeValue_id) {
			this.AttributeValue_id = arguments[0].AttributeValue_id;
		}

		 switch (this.action) {
			 case 'add':
				 win.setTitle(lang['znachenie_dobavlenie']);
				 win.enableEdit(true);
				 break;

			 case 'edit':
				 win.setTitle(lang['znachenie_redaktirovanie']);
				 win.enableEdit(true);
				 break;

			 case 'view':
				 win.setTitle(lang['znachenie_prosmotr']);
				 win.enableEdit(false);
				 break;
		 }

		this.getLoadMask(lang['zagruzka_parametrov']).show();
		Ext.Ajax.request({
			url: '/?c=TariffVolumes&m=getValuesFields',
			params:
			{
				AttributeVision_TableName: win.AttributeVision_TableName,
				AttributeVision_TablePKey: win.AttributeVision_TablePKey,
				AttributeValue_id: win.AttributeValue_id
			},
			scope: this,
			callback: function(o, s, r) {
				this.getLoadMask().hide();
				if( s ) {
					var obj = Ext.util.JSON.decode(r.responseText);
					if(obj.data) {
						this.setFormFields(obj.data);
						// прогрузить данные комбо-справочников
						var panel = this.FormPanel; // получаем панель, на которой находятся комбики
						this.getFieldsLists(panel, {
							needConstructComboLists: true,
							needConstructEditFields: true
						});

						var base_form = this.FormPanel.getForm();

						if ( !Ext.isEmpty(win.findById(win.id + '_AttributeValue_begDate').getValue()) && !Ext.isEmpty(win.findById(win.id + '_AttributeValue_begDate').UslugaComplexComboName) ) {
							win.findById(win.id + '_' + win.findById(win.id + '_AttributeValue_begDate').UslugaComplexComboName).getStore().baseParams.UslugaComplex_Date = win.findById(win.id + '_AttributeValue_begDate').getValue().format('d.m.Y');
						}

						this.loadDataLists({}, this.lists, true, function() {
							// Для списков сперва формируем массив, затем в цикле загружаем
							// @task https://redmine.swan.perm.ru/issues/81632
							var comboToLoadList = new Array();

							win.applyDateFilter();

							for (var k in obj.data) {
								if (!Ext.isEmpty(win.findById(win.id + '_' + obj.data[k].name))) {
									if (obj.data[k].table) {
										switch(obj.data[k].table) {
											case 'UslugaComplex':
												var combo = win.findById(win.id + '_' + obj.data[k].name);
												if (!Ext.isEmpty(obj.data[k].value) && !combo.isOtkazVolume) {
													comboToLoadList.push({
														name: obj.data[k].name,
														params: {
															'UslugaComplex_id': obj.data[k].value
														},
														value: obj.data[k].value
													});
												}
												if (!Ext.isEmpty(win.AIS)) {
													combo.allowBlank = false;
													combo.validate();
												}
												break;
											case 'DrugNomen':
												if (!Ext.isEmpty(obj.data[k].value)) {
													comboToLoadList.push({
														name: obj.data[k].name,
														params: {
															'DrugNomen_id': obj.data[k].value
														},
														value: obj.data[k].value
													});
												}
												break;
											case 'Org':
												if (!Ext.isEmpty(obj.data[k].value)) {
													comboToLoadList.push({
														name: obj.data[k].name,
														params: {
															'Org_id': obj.data[k].value
														},
														value: obj.data[k].value
													});
												}
												break;
											case 'MesOld':
												if (!Ext.isEmpty(obj.data[k].value)) {
													comboToLoadList.push({
														name: obj.data[k].name,
														params: {
															'Mes_id': obj.data[k].value
														},
														value: obj.data[k].value
													});
												}
												break;
											case 'Diag':
												comboToLoadList.push({
													name: obj.data[k].name,
													params: {
														where: "where Diag_id = " + obj.data[k].value
													},
													value: obj.data[k].value
												});
												break;
											case 'SurveyType':
												var combo = win.findById(win.id + '_' + obj.data[k].name);
												
												if ( !Ext.isEmpty(combo.UslugaComplexComboName) ) {
													win.findById(win.id + '_' + combo.UslugaComplexComboName).getStore().baseParams.SurveyType_id = obj.data[k].value;
												}
												break;
											case 'DispClass':
												var combo = win.findById(win.id + '_' + obj.data[k].name),
													DispType_id="";

												if(combo.isOrp13ProfTeen){
													DispType_id = obj.data[k].value == 10 ? 4 : 2;
												}
												
												if ( !Ext.isEmpty(combo.AgeGroupDispComboName)) {
													var params = {
														name: 'AgeGroupDisp',
														atributid: combo.AgeGroupDispComboName,
														DispType_id: DispType_id
												};

												win.applyFilterCombo(params);
											}
											break;
											case 'LpuSectionCode':
												var combo = base_form.findField(obj.data[k].name);
												combo.tpl = new Ext.XTemplate(
													'<tpl for="."><div class="x-combo-list-item">',
													'<table style="border: 0;"><td style="width: 70px"><font color="red">{LpuSectionCode_Code}</font></td><td><div><h3>{LpuSectionCode_Name}</h3></div>&nbsp{LpuSectionCode_begDT}&nbsp{LpuSectionCode_endDT}</td></tr></table>',
													'</div></tpl>'
												);
												break;
											default:
												win.findById(win.id + '_' + obj.data[k].name).setValue(obj.data[k].value);
												break;
										}
									} else {
										win.findById(win.id + '_' + obj.data[k].name).setValue(obj.data[k].value);
									}
								}
							}

							if ( comboToLoadList.length > 0 ) {
								var i = 0;

								var loadList = function(data) {
									var combo = win.findById(win.id + '_' + data.name);
									var combo_value = data.value;
									combo.getStore().load({
										params: data.params,
										callback: function() {
											combo.setValue(combo_value);
											i++;

											if ( i < comboToLoadList.length ) {
												loadList(comboToLoadList[i]);
											}
										}
									});
								}

								loadList(comboToLoadList[i]);
							}
						}); // прогружаем все справочники (третий параметр noclose - без операций над формой)
					} else {
						this.hide();
					}
				}
			}
		});
	},

	initComponent: function() {
		this.mainPanel = new Ext.Panel({
			bodyBorder: false,
			border: false,
			labelWidth: 150,
			layout: 'form',
			items: [ ]
		});

		Ext.apply(this, {
			items: [
				this.mainPanel
			],
			buttons: [
				{
					text: BTN_FRMSAVE,
					tooltip: lang['sohranit'],
					iconCls: 'save16',
					handler: function()
					{
						this.doSave();
					}.createDelegate(this)
				}, {
					text: '-'
				},
				HelpButton(this, 1),
				{
					handler: function () {
						this.hide();
					}.createDelegate(this),
					iconCls: 'cancel16',
					text: lang['otmenit']
				}]
		});

		sw.Promed.swAttributeValueEditWindow.superclass.initComponent.apply(this, arguments);
	},

	_checkDatePeriod: function()
	{
		var base_form = this.FormPanel.getForm(),
			dtBegDate = base_form.findField('AttributeValue_begDate'),
			dtEndDate = base_form.findField('AttributeValue_endDate'),
			begDate,
			endDate;

		if (dtBegDate && dtEndDate &&
			(begDate = dtBegDate.getValue()) && (endDate = dtEndDate.getValue()) &&
			begDate > endDate)
		{
			dtEndDate.setValue(begDate);
			return (true);
		}

		return (false);
	}
});