/**
 * swEvnUslugaOnkoBeamEditWindow - окно редактирования "Лучевое лечение"
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

Ext6.define('common.MorbusOnko.swEvnUslugaOnkoBeamEditWindow', {
	/* свойства */
	requires: [
		'common.MorbusOnko.AddOnkoComplPanel',
		'common.EMK.PersonInfoPanelShort',
	],
	alias: 'widget.swEvnUslugaOnkoBeamEditWindow',
    autoShow: false,
	closable: true,
	cls: 'arm-window-new emkd',
	constrain: true,
	extend: 'base.BaseForm',
	findWindow: false,
    header: true,
	modal: true,
	layout: 'form',
	refId: 'EvnUslugaOnkoBeameditsw',
	renderTo: Ext.getCmp('main-center-panel').body.dom,
	resizable: false,
	title: 'Лучевое лечение',
	width: 820,
	maxHeight: main_center_panel.body.getHeight() - 50,

	disabledDatePeriods: null,
	setAllowedDates: function() {
		var win = this;
		var base_form = this.FormPanel.getForm();
		var set_dt_field = base_form.findField('EvnUslugaOnkoBeam_setDate');
		var morbus_id = base_form.findField('Morbus_id').getValue();
        var morbusonkovizitpldop_id = win.MorbusOnkoVizitPLDop_id;
        var morbusonkoleave_id = win.MorbusOnkoLeave_id;
        var morbusonkodiagplstom_id = win.MorbusOnkoDiagPLStom_id;

		win.disabledDatePeriods = null;

		if (morbus_id) {
			win.mask(LOAD_WAIT);
			Ext.Ajax.request({
				failure:function () {
					sw.swMsg.alert(langs('Ошибка'), langs('Не удалось получить данные с сервера'));
					win.unmask();
				},
				params: {
					Morbus_id: morbus_id,
                    MorbusOnkoVizitPLDop_id: morbusonkovizitpldop_id,
                    MorbusOnkoLeave_id: morbusonkoleave_id,
                    MorbusOnkoDiagPLStom_id: morbusonkodiagplstom_id
				},
				method: 'POST',
				success: function (response) {
					win.unmask();
					var result = Ext.util.JSON.decode(response.responseText);
					if (result[0] && Ext.isArray(result[0].disabledDatePeriods) && result[0].disabledDatePeriods.length > 0) {
						win.disabledDatePeriods = result[0].disabledDatePeriods;
						// в поле set_dt_field даём выбирать только те, что подходят к одному из периодов
						var disabledDates = [];
						for(var k in win.disabledDatePeriods) {
							if (typeof win.disabledDatePeriods[k] == 'object') {
								for (var k2 in win.disabledDatePeriods[k]) {
									if (typeof win.disabledDatePeriods[k][k2] == 'string') {
										disabledDates.push(win.disabledDatePeriods[k][k2]);
									}
								}
							}
						}
						//set_dt_field.setAllowedDates(disabledDates);
						win.setAllowedDatesForDisField();
					} else {
						//set_dt_field.setAllowedDates(null);
						win.setAllowedDatesForDisField();
					}
				},
				url:'/?c=MorbusOnkoSpecifics&m=getMorbusOnkoSpecTreatDisabledDates'
			});
		} else {
			//set_dt_field.setAllowedDates(null);
			win.setAllowedDatesForDisField();
		}
	},
	setAllowedDatesForDisField: function() {
		var win = this;
		var base_form = this.FormPanel.getForm();
		var set_dt_field = base_form.findField('EvnUslugaOnkoBeam_setDate');
		var set_dt_value = null;
		if (!Ext.isEmpty(set_dt_field.getValue())) {
			set_dt_value = set_dt_field.getValue().format('d.m.Y');
		}
		var dis_dt_field = base_form.findField('EvnUslugaOnkoBeam_disDate');

		//dis_dt_field.setAllowedDates(null);

		if (Ext.isArray(win.disabledDatePeriods) && win.disabledDatePeriods.length > 0) {
			// в поле dis_dt_field даём выбирать только те, что подходят к одному из периодов соответствующим полю set_dt
			var disabledDates = [];
			for(var k in win.disabledDatePeriods) {
				if (typeof win.disabledDatePeriods[k] == 'object') {
					if (Ext.isEmpty(set_dt_value) || set_dt_value.inlist(win.disabledDatePeriods[k])) {
						for (var k2 in win.disabledDatePeriods[k]) {
							if (typeof win.disabledDatePeriods[k][k2] == 'string') {
								disabledDates.push(win.disabledDatePeriods[k][k2]);
							}
						}
					}
				}
			}
			//dis_dt_field.setAllowedDates(disabledDates);
		}
	},

	/* методы */
	save: function () {
		var
			base_form = this.FormPanel.getForm(),
			win = this;

		if ( !base_form.isValid() ) {
			sw.swMsg.alert('Ошибка', 'Не все поля формы заполнены корректно');
			return false;
		}

		win.mask(LOAD_WAIT_SAVE);
		
        var params = {};
        params.action = win.action;
		if (this.EvnPL_id)
			params.EvnPL_id = this.EvnPL_id;
        var AggTypes = this.AggTypePanel.getValues();
        params.AggTypes = (AggTypes.length > 1 ? AggTypes.join(',') : AggTypes);

		base_form.submit({
			params: params,
			success: function(form, action) {
				win.unmask();

				if ( !Ext6.isEmpty(action.result.Error_Msg) ) {
					sw.swMsg.alert(langs('Ошибка'), action.result.Error_Msg);
					return false;
				}

				win.callback();
				win.hide();
			},
			failure: function(form, action) {
				win.unmask();
			}
		});
	},
	setOnkoRadiotherapyFilter: function() {
		var
			base_form = this.FormPanel.getForm(),
			EvnUslugaOnkoBeam_setDate = base_form.findField('EvnUslugaOnkoBeam_setDate').getValue(),
			index = -1,
			OnkoRadiotherapy_id = base_form.findField('OnkoRadiotherapy_id').getValue();

		if ( Ext.isEmpty(EvnUslugaOnkoBeam_setDate) ) {
			EvnUslugaOnkoBeam_setDate = getValidDT(getGlobalOptions().date, '');
		}

		base_form.findField('OnkoRadiotherapy_id').getStore().clearFilter();
		base_form.findField('OnkoRadiotherapy_id').clearValue();
		base_form.findField('OnkoRadiotherapy_id').lastQuery = '';

		base_form.findField('OnkoRadiotherapy_id').getStore().filterBy(function(rec) {
			return (
				(!rec.get('OnkoRadiotherapy_begDate') || rec.get('OnkoRadiotherapy_begDate') <= EvnUslugaOnkoBeam_setDate)
				&& (!rec.get('OnkoRadiotherapy_endDate') || rec.get('OnkoRadiotherapy_endDate') >= EvnUslugaOnkoBeam_setDate)
			)
		});

		if ( !Ext.isEmpty(OnkoRadiotherapy_id) ) {
			index = base_form.findField('OnkoRadiotherapy_id').getStore().findBy(function(rec) {
				return rec.get('OnkoRadiotherapy_id') == OnkoRadiotherapy_id;
			});
		}

		if ( index >= 0 ) {
			base_form.findField('OnkoRadiotherapy_id').setValue(OnkoRadiotherapy_id);
		}
	},
	setOnkoGormunFilter : function() {
		var
			base_form = this.FormPanel.getForm(),
			EvnUslugaOnkoGormun_setDate = base_form.findField('EvnUslugaOnkoGormun_setDate');

			if(EvnUslugaOnkoGormun_setDate) {
				EvnUslugaOnkoGormun_setDate = EvnUslugaOnkoGormun_setDate.getValue();

				if ( Ext.isEmpty(EvnUslugaOnkoGormun_setDate) ) {
					EvnUslugaOnkoGormun_setDate = getValidDT(getGlobalOptions().date, '');
				}

				base_form.findField('EvnUslugaOnkoGormun_id').getStore().filterBy(function(rec) {
					return (
						(!rec.get('EvnUslugaOnkoGormun_begDate') || rec.get('EvnUslugaOnkoGormun_begDate') >= EvnUslugaOnkoGormun_setDate)
						&& (!rec.get('EvnUslugaOnkoGormun_endDate') || rec.get('EvnUslugaOnkoGormun_endDate') <= EvnUslugaOnkoGormun_setDate)
					)
				});
			}
		
	},
	setUslugaComplexFilter: function() {
		var
			base_form = this.FormPanel.getForm(),
			UslugaCategory_SysNick = base_form.findField('UslugaCategory_id').getFieldValue('UslugaCategory_SysNick'),
			UslugaComplex_Date = base_form.findField('EvnUslugaOnkoBeam_setDate').getValue();

		if (
			(
				Ext.isEmpty(UslugaCategory_SysNick)
				|| base_form.findField('UslugaComplex_id').getStore().proxy.extraParams.uslugaCategoryList == Ext.util.JSON.encode([ UslugaCategory_SysNick ])
			)
			&& (
				typeof UslugaComplex_Date != 'object'
				|| base_form.findField('UslugaComplex_id').getStore().proxy.extraParams.UslugaComplex_Date == Ext.util.Format.date(UslugaComplex_Date, 'd.m.Y')
			)
		) {
			return false;
		}

		base_form.findField('UslugaComplex_id').clearValue();
		base_form.findField('UslugaComplex_id').getStore().removeAll();
		base_form.findField('UslugaComplex_id').lastQuery = 'This query sample win is not will never appear';

		base_form.findField('UslugaComplex_id').getStore().proxy.extraParams.UslugaComplex_Date = Ext.util.Format.date(UslugaComplex_Date, 'd.m.Y');
		base_form.findField('UslugaComplex_id').setUslugaCategoryList([ UslugaCategory_SysNick ]);
	},
	onSprLoad: function(arguments) {

		var win = this;
		base_form = win.FormPanel.getForm();

		this.action = '';
		this.callback = Ext.emptyFn;
		this.EvnUslugaOnkoBeam_id = null;
        if ( !arguments[0] ) {
            sw.swMsg.alert(langs('Ошибка'), langs('Не указаны входные данные'), function() { win.hide(); });
            return false;
        }
		if ( arguments[0].action ) {
			this.action = arguments[0].action;
		}
		if ( arguments[0].ARMType ) {
			this.ARMType = arguments[0].ARMType;
		}
		if ( arguments[0].callback && typeof arguments[0].callback == 'function' ) {
			this.callback = arguments[0].callback;
		}
		if ( arguments[0].owner ) {
			this.owner = arguments[0].owner;
		}
		if ( arguments[0].EvnUslugaOnkoBeam_id ) {
			this.EvnUslugaOnkoBeam_id = arguments[0].EvnUslugaOnkoBeam_id;
		}
		if (!Ext.isEmpty(arguments[0].formParams.EvnPL_id))
			this.EvnPL_id = arguments[0].formParams.EvnPL_id;

        this.MorbusOnkoVizitPLDop_id = arguments[0].MorbusOnkoVizitPLDop_id || null;
        this.MorbusOnkoLeave_id = arguments[0].MorbusOnkoLeave_id || null;
        this.MorbusOnkoDiagPLStom_id = arguments[0].MorbusOnkoDiagPLStom_id || null;
		base_form.reset();
		
		base_form.findField('UslugaCategory_id').setContainerVisible(getRegionNick() != 'kz');
		base_form.findField('UslugaComplex_id').setContainerVisible(getRegionNick() != 'kz');

		base_form.findField('UslugaComplex_id').setAllowedUslugaComplexAttributeList([ 'LuchLech' ]);
		
		base_form.findField('UslugaCategory_id').getStore().filterBy(function(rec) {
			return rec.get('UslugaCategory_SysNick').inlist(['gost2011','lpu','tfoms']);
		});

		switch (arguments[0].action) {
			case 'add':
				this.setTitle(langs('Лучевое лечение: Добавление'));
				//this.setFieldsDisabled(false);
				break;
			case 'edit':
				this.setTitle(langs('Лучевое лечение: Редактирование'));
				//this.setFieldsDisabled(false);
				break;
			case 'view':
				this.setTitle(langs('Лучевое лечение: Просмотр'));
				//this.setFieldsDisabled(true);
				break;
		}
		
		win.PersonInfoPanel.load({
			Person_id: arguments[0].formParams.Person_id
		});

        win.mask(LOAD_WAIT);
		switch (arguments[0].action) {
			case 'add':
				base_form.setValues(arguments[0].formParams);
				win.unmask();
				win.setAllowedDates();
				this.AggTypePanel.setValues([null]);
				if ( getRegionNick() != 'kz' ) {
					base_form.findField('UslugaCategory_id').setFieldValue('UslugaCategory_SysNick', 'gost2011');
					win.setUslugaComplexFilter();
				}
				win.setOnkoRadiotherapyFilter();
				win.setOnkoGormunFilter();
				base_form.isValid();
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
						EvnUslugaOnkoBeam_id: win.EvnUslugaOnkoBeam_id
					},
					success: function (response) {
                        win.unmask();
						var result = Ext.util.JSON.decode(response.responseText);
						if (result[0]) {
                            base_form.setValues(result[0]);
                            if(result[0].AggTypes){
								win.AggTypePanel.setValues(result[0].AggTypes);
							} else {
								win.AggTypePanel.setValues([null]);
							}

							var UslugaComplex_id = result[0].UslugaComplex_id || null;
							
							win.setOnkoRadiotherapyFilter();
							win.setOnkoGormunFilter();
							win.setUslugaComplexFilter();
							
							if ( !Ext.isEmpty(UslugaComplex_id) ) {
								base_form.findField('UslugaComplex_id').getStore().load({
									callback: function() {
										if ( base_form.findField('UslugaComplex_id').getStore().getCount() > 0 ) {
											base_form.findField('UslugaComplex_id').setValue(UslugaComplex_id);
										}
										else {
											base_form.findField('UslugaComplex_id').clearValue();
										}
									}.createDelegate(this),
									params: {
										UslugaComplex_id: UslugaComplex_id
									}
								});
							}
							win.setAllowedDates();
							base_form.isValid();
                        }
					},
					url:'/?c=EvnUslugaOnkoBeam&m=load'
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
			style: 'border-bottom: 1px solid #d0d0d0;',
			ownerWin: this
		});
		
		win.AggTypePanel = Ext6.create('common.MorbusOnko.AddOnkoComplPanel', {
			objectName: 'AggType',
			fieldLabelTitle: langs('Осложнение'),
			win: this,
			width: 740,
			buttonAlign: 'left',
			buttonLeftMargin: 150,
			fieldWidth: 700,
			labelWidth: 200
		});

		win.FormPanel = new Ext6.form.FormPanel({
			autoScroll: true,
			border: false,
			bodyPadding: '15 25 15 37',
			defaults: {
				labelAlign: 'left',
				labelWidth: 200
			},
			reader: Ext6.create('Ext6.data.reader.Json', {
				type: 'json',
				model: win.id + '_FormModel'
			}),
			url: '/?c=EvnUslugaOnkoBeam&m=save',
			items: [{
				name: 'EvnUslugaOnkoBeam_id',
				xtype: 'hidden',
				value: 0
			}, {
				name: 'EvnUslugaOnkoBeam_pid',
				xtype: 'hidden',
				value: 0
			}, {
				name: 'Morbus_id',
				xtype: 'hidden',
				value: 0
			}, {
				name: 'Server_id',
				xtype: 'hidden'
			}, {
				name: 'PersonEvn_id',
				xtype: 'hidden'
			}, {
				name: 'Person_id',
				xtype: 'hidden'
			}, {
				width: 700,
				bodyPadding: '0 0 5 0',
				border: false,
				defaults: {
					labelAlign: 'left',
					labelWidth: 200
				},
				layout: 'column',
				columns: [0.4, 0.4],
				items: [{
					fieldLabel: langs('Дата начала'),
					name: 'EvnUslugaOnkoBeam_setDate',
					allowBlank: false,
					xtype: 'swDateField',
					listeners: {
						'change': function(field){
							win.setAllowedDatesForDisField();
							win.setUslugaComplexFilter();
							win.setOnkoRadiotherapyFilter();
							win.setOnkoGormunFilter();
						}
					},
				}, {
					labelAlign: 'right',
					labelWidth: 80,
					allowBlank: false,
					fieldLabel: langs('Время'),
					name: 'EvnUslugaOnkoBeam_setTime',
					width: 200,
					xtype: 'swTimeField'
				}]
			}, {
				width: 700,
				bodyPadding: '0 0 5 0',
				border: false,
				defaults: {
					labelAlign: 'left',
					labelWidth: 200
				},
				layout: 'column',
				columns: [0.4, 0.4],
				items: [{
					fieldLabel: langs('Дата окончания'),
					name: 'EvnUslugaOnkoBeam_disDate',
					xtype: 'swDateField'
				}, {
					labelAlign: 'right',
					labelWidth: 80,
					fieldLabel: langs('Время'),
					name: 'EvnUslugaOnkoBeam_disTime',
					width: 200,
					xtype: 'swTimeField'
				}]
			}, {
				allowBlank: !getRegionNick().inlist([ 'astra', 'kareliya', 'penza', 'perm', 'ufa' ]),
				comboSubject: 'UslugaCategory',
				fieldLabel: langs('Категория услуги'),
				name: 'UslugaCategory_id',
				listeners: {
					'change': function(combo, newValue, oldValue) {
						var idx = combo.getStore().findBy(function(rec) {
							return rec.get('UslugaCategory_id') == newValue;
						});
						combo.fireEvent('select', combo, combo.getStore().getAt(idx), idx);
					},
					'select': function(combo, record) {
						win.setUslugaComplexFilter();
					}
				},
				width: 700,
				xtype: 'commonSprCombo'
			}, {
				allowBlank: !getRegionNick().inlist([ 'astra', 'kareliya', 'penza', 'perm', 'ufa' ]),
				fieldLabel: langs('Название услуги'),
				name: 'UslugaComplex_id',
				listWidth: 700,
				to: 'EvnUslugaOnkoBeam',
				width: 700,
				xtype: 'swUslugaComplexCombo'
			}, {
				fieldLabel: langs('Способ облучения'),
				name: 'OnkoUslugaBeamIrradiationType_id',
				xtype: 'commonSprCombo',
				allowBlank: false,
				sortField:'OnkoUslugaBeamIrradiationType_Code',
				comboSubject: 'OnkoUslugaBeamIrradiationType',
				typeCode: 'int',
				width: 700
			}, {
				fieldLabel: langs('Вид лучевой терапии'),
				name: 'OnkoUslugaBeamKindType_id',
				xtype: 'commonSprCombo',
				allowBlank: false,
				sortField:'OnkoUslugaBeamKindType_Code',
				comboSubject: 'OnkoUslugaBeamKindType',
				typeCode: 'int',
				width: 700
			}, {
				fieldLabel: langs('Метод лучевой терапии'),
				name: 'OnkoUslugaBeamMethodType_id',
				xtype: 'commonSprCombo',
				allowBlank: false,
				sortField:'OnkoUslugaBeamMethodType_Code',
				comboSubject: 'OnkoUslugaBeamMethodType',
				typeCode: 'int',
				width: 700
			}, {
				fieldLabel: langs('Радиомодификаторы'),
				name: 'OnkoUslugaBeamRadioModifType_id',
				xtype: 'commonSprCombo',
				allowBlank: true,
				sortField:'OnkoUslugaBeamRadioModifType_Code',
				comboSubject: 'OnkoUslugaBeamRadioModifType',
				width: 700
			}, {
				fieldLabel: langs('Преимущественная направленность лучевой терапии'),
				name: 'OnkoUslugaBeamFocusType_id',
				xtype: 'commonSprCombo',
				allowBlank: false,
				sortField:'OnkoUslugaBeamFocusType_Code',
				comboSubject: 'OnkoUslugaBeamFocusType',
				width: 700
			}, {
				fieldLabel: langs('Вид планирования'),
				xtype: 'commonSprCombo',
				comboSubject: 'OnkoPlanType',
				name: 'OnkoPlanType_id',
				width: 700
			}, {
				fieldLabel: langs('Место выполнения'),
				autoLoad: true,
				name: 'Lpu_uid',
				allowBlank: !getRegionNick().inlist([ 'kareliya', 'perm', 'ufa' ]),
				xtype: 'swLpuCombo',
				width: 700
			}, {
				comboSubject: 'OnkoTreatType',
				fieldLabel: langs('Характер лечения'),
				name: 'OnkoTreatType_id',
				sortField:'OnkoTreatType_Code',
				width: 700,
				xtype: 'commonSprCombo'
			}, {
				allowBlank: false,
				comboSubject: 'OnkoRadiotherapy',
				name: 'OnkoRadiotherapy_id',
				fieldLabel: langs('Тип лечения'),
				moreFields: [
					{name: 'OnkoRadiotherapy_begDate', mapping: 'OnkoRadiotherapy_begDate', type: 'date', dateFormat: 'd.m.Y'},
					{name: 'OnkoRadiotherapy_endDate', mapping: 'OnkoRadiotherapy_endDate', type: 'date', dateFormat: 'd.m.Y'}
				],
				width: 700,
				xtype: 'commonSprCombo'
			}, {
				fieldLabel: langs('Условие проведения лечения'),
				name: 'TreatmentConditionsType_id',
				comboSubject: 'TreatmentConditionsType',
				allowBlank: true,
				xtype: 'commonSprCombo',
				width: 700
			},
			win.AggTypePanel, 
			{
				allowBlank: false,
				allowDecimals: false,
				minValue: 0,
				fieldLabel: langs('Кол-во фракций проведения лучевой терапии'),
				name: 'EvnUslugaOnkoBeam_CountFractionRT',
				autoCreate: {tag: "input", maxLength: "8", autocomplete: "off"},
				width: 300,
				xtype: 'numberfield'
			}, {
				layout: 'column',
				width: 700,
				border: false,
				style: 'margin-bottom: 5px;',
				items: [{
					allowBlank: !getRegionNick().inlist([ 'kareliya', 'penza', 'perm', 'ufa', 'adygeya' ]),
					minValue: 0,
					fieldLabel: langs('Суммарная доза облучения опухоли'),
					name: 'EvnUslugaOnkoBeam_TotalDoseTumor',
					xtype: 'numberfield',
					autoCreate: {tag: "input", maxLength: "8", autocomplete: "off"},
					labelWidth: 430,
					style: 'margin-right: 5px;',
					width: 545
				}, {
					name: 'OnkoUslugaBeamUnitType_id',
					hideLabel: true,
					comboSubject: 'OnkoUslugaBeamUnitType',
					xtype: 'commonSprCombo',
					displayCode: false,
					value: 1,
					width: 150
				}]
			}, {
				layout: 'column',
				width: 700,
				border: false,
				items: [{
					fieldLabel: langs('Суммарная доза облучения зон регионарного метастазирования'),
					minValue: 0,
					name: 'EvnUslugaOnkoBeam_TotalDoseRegZone',
					xtype: 'numberfield',
					autoCreate: {tag: "input", maxLength: "8", autocomplete: "off"},
					labelWidth: 430,
					style: 'margin-right: 5px;',
					width: 545
				}, {
					name: 'OnkoUslugaBeamUnitType_did',
					hideLabel: true,
					comboSubject: 'OnkoUslugaBeamUnitType',
					xtype: 'commonSprCombo',
					displayCode: false,
					value: 1,
					width: 150
				}]
			}]
		});

        Ext6.apply(win, {
			items: [
				win.PersonInfoPanel, {
					userCls: 'mini-scroll',
                    xtype: 'panel',
					layout: 'form',
					overflowY: 'auto',
					border: false,
					maxHeight: main_center_panel.body.getHeight() - 175,
					items: [
						win.FormPanel
					]
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
    }
});