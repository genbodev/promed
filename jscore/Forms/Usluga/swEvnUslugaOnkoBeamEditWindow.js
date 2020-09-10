/**
 * swEvnUslugaOnkoBeamEditWindow - окно редактирования "Лучевое лечение"
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      MorbusOnko
 * @access       public
 * @copyright    Copyright (c) 2013 Swan Ltd.
 * @version      06.2013
 * @comment
 */

sw.Promed.swEvnUslugaOnkoBeamEditWindow = Ext.extend(sw.Promed.BaseForm, {
	action: null,
	autoHeight: true,
	autoScroll: true,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	draggable: true,
	formMode: 'remote',
	formStatus: 'edit',
	layout: 'form',
	modal: true,
	width: 800,
	listeners: {
		hide: function() {
			this.onHide();
		}
	},
	onHide: Ext.emptyFn,
	doSave:  function() {
		var thas = this;
		if ( !this.form.isValid() )
		{
			sw.swMsg.show(
			{
				buttons: Ext.Msg.OK,
				fn: function() 
				{
					thas.findById('EvnUslugaOnkoBeamEditForm').getFirstInvalidEl().focus(true);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		if ( getRegionNick() == 'astra' ) {
			if (
				Ext.isEmpty(this.form.findField('EvnUslugaOnkoBeam_TotalDoseTumor').getValue())
				&& Ext.isEmpty(this.form.findField('EvnUslugaOnkoBeam_TotalDoseRegZone').getValue())
			) {
				sw.swMsg.alert(langs('Ошибка'), langs('Одно из полей "Суммарная доза облучения опухоли" или "Суммарная доза облучения зон регионарного метастазирования" обязательно для заполнения'));
				return false;
			}
		}

        this.submit();
		return true;		
	},
	submit: function() {
		var thas = this;
		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение..."});
		loadMask.show();
		var params = {};
		params.action = thas.action;
		if (this.EvnPL_id)
			params.EvnPL_id = this.EvnPL_id;
		var AggTypes = this.AggTypePanel.getValues();
		params.AggTypes = (AggTypes.length > 1 ? AggTypes.join(',') : AggTypes);
		this.form.submit({
			params: params,
			failure: function(result_form, action) 
			{
				loadMask.hide();
				if (action.result) 
				{
					if (action.result.Error_Code)
					{
						sw.swMsg.alert(langs('Ошибка #')+action.result.Error_Code, action.result.Error_Message);
					}
				}
			},
			success: function(result_form, action)
			{
				if (action.result.EvnUslugaCommon_id) {
					if (action.result.EvnUslugaCommon_id == -1) {
						showSysMsg('Добавленная услуга уже имеется в текущем случае лечения и не будет внесена повторно');
					}
					else {
						showSysMsg('Вы добавили новую услугу. Услуга скопирована в раздел "Услуги" текущего посещения / движения');
					}
				}
				loadMask.hide();
				thas.callback(thas.owner, action.result.EvnUslugaOnkoBeam_id);
				thas.hide();
			}
		});
	},
	setFieldsDisabled: function(d) 
	{
		var form = this;
		this.form.items.each(function(f) 
		{
			if (f && (f.xtype!='hidden') && (f.xtype!='fieldset')  && (f.changeDisabled!==false))
			{
				f.setDisabled(d);
			}
		});
		form.buttons[0].setDisabled(d);
	},
	disabledDatePeriods: null,
	setAllowedDates: function() {
		var that = this;
		var set_dt_field = that.form.findField('EvnUslugaOnkoBeam_setDate');
		var morbus_id = that.form.findField('Morbus_id').getValue();
        var morbusonkovizitpldop_id = that.MorbusOnkoVizitPLDop_id;
        var morbusonkoleave_id = that.MorbusOnkoLeave_id;
        var morbusonkodiagplstom_id = that.MorbusOnkoDiagPLStom_id;

		that.disabledDatePeriods = null;

		if (morbus_id) {
			var loadMask = new Ext.LoadMask(this.form.getEl(), {msg:lang['zagruzka']});
			loadMask.show();
			Ext.Ajax.request({
				failure:function () {
					sw.swMsg.alert(lang['oshibka'], lang['ne_udalos_poluchit_dannyie_s_servera']);
					loadMask.hide();
				},
				params: {
					Morbus_id: morbus_id,
                    MorbusOnkoVizitPLDop_id: morbusonkovizitpldop_id,
                    MorbusOnkoLeave_id: morbusonkoleave_id,
                    MorbusOnkoDiagPLStom_id: morbusonkodiagplstom_id
				},
				method: 'POST',
				success: function (response) {
					loadMask.hide();
					var result = Ext.util.JSON.decode(response.responseText);
					if (result[0] && Ext.isArray(result[0].disabledDatePeriods) && result[0].disabledDatePeriods.length > 0) {
						that.disabledDatePeriods = result[0].disabledDatePeriods;
						// в поле set_dt_field даём выбирать только те, что подходят к одному из периодов
						var disabledDates = [];
						for(var k in that.disabledDatePeriods) {
							if (typeof that.disabledDatePeriods[k] == 'object') {
								for (var k2 in that.disabledDatePeriods[k]) {
									if (typeof that.disabledDatePeriods[k][k2] == 'string') {
										disabledDates.push(that.disabledDatePeriods[k][k2]);
									}
								}
							}
						}
						set_dt_field.setAllowedDates(disabledDates);
						that.setAllowedDatesForDisField();
					} else {
						set_dt_field.setAllowedDates(null);
						that.setAllowedDatesForDisField();
					}
				},
				url:'/?c=MorbusOnkoSpecifics&m=getMorbusOnkoSpecTreatDisabledDates'
			});
		} else {
			set_dt_field.setAllowedDates(null);
			that.setAllowedDatesForDisField();
		}
	},
	setAllowedDatesForDisField: function() {
		var that = this;
		var set_dt_field = that.form.findField('EvnUslugaOnkoBeam_setDate');
		var set_dt_value = null;
		if (!Ext.isEmpty(set_dt_field.getValue())) {
			set_dt_value = set_dt_field.getValue().format('d.m.Y');
		}
		var dis_dt_field = that.form.findField('EvnUslugaOnkoBeam_disDate');

		dis_dt_field.setAllowedDates(null);

		if (Ext.isArray(that.disabledDatePeriods) && that.disabledDatePeriods.length > 0) {
			// в поле dis_dt_field даём выбирать только те, что подходят к одному из периодов соответствующим полю set_dt
			var disabledDates = [];
			for(var k in that.disabledDatePeriods) {
				if (typeof that.disabledDatePeriods[k] == 'object') {
					if (Ext.isEmpty(set_dt_value) || set_dt_value.inlist(that.disabledDatePeriods[k])) {
						for (var k2 in that.disabledDatePeriods[k]) {
							if (typeof that.disabledDatePeriods[k][k2] == 'string') {
								disabledDates.push(that.disabledDatePeriods[k][k2]);
							}
						}
					}
				}
			}
			dis_dt_field.setAllowedDates(disabledDates);
		}
	},
	setOnkoRadiotherapyFilter: function() {
		var
			base_form = this.form,
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

		base_form.findField('OnkoRadiotherapy_id').setBaseFilter(function(rec) {
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
	setUslugaComplexFilter: function() {
		var
			base_form = this.form,
			UslugaCategory_SysNick = base_form.findField('UslugaCategory_id').getFieldValue('UslugaCategory_SysNick'),
			UslugaComplex_Date = base_form.findField('EvnUslugaOnkoBeam_setDate').getValue();

		if (
			(
				Ext.isEmpty(UslugaCategory_SysNick)
				|| base_form.findField('UslugaComplex_id').getStore().baseParams.uslugaCategoryList == Ext.util.JSON.encode([ UslugaCategory_SysNick ])
			)
			&& (
				typeof UslugaComplex_Date != 'object'
				|| base_form.findField('UslugaComplex_id').getStore().baseParams.UslugaComplex_Date == Ext.util.Format.date(UslugaComplex_Date, 'd.m.Y')
			)
		) {
			return false;
		}

		base_form.findField('UslugaComplex_id').clearValue();
		base_form.findField('UslugaComplex_id').getStore().removeAll();
		base_form.findField('UslugaComplex_id').lastQuery = 'This query sample that is not will never appear';

		base_form.findField('UslugaComplex_id').getStore().baseParams.UslugaComplex_Date = Ext.util.Format.date(UslugaComplex_Date, 'd.m.Y');
		base_form.findField('UslugaComplex_id').setUslugaCategoryList([ UslugaCategory_SysNick ]);
	},
	show: function() {
        var thas = this;
		sw.Promed.swEvnUslugaOnkoBeamEditWindow.superclass.show.apply(this, arguments);		
		this.action = '';
		this.callback = Ext.emptyFn;
		this.EvnUslugaOnkoBeam_id = null;
        if ( !arguments[0] ) {
            sw.swMsg.alert(lang['oshibka'], lang['ne_ukazanyi_vhodnyie_dannyie'], function() { thas.hide(); });
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
		this.form.reset();
		
		this.form.findField('UslugaCategory_id').setContainerVisible(getRegionNick() != 'kz');
		this.form.findField('UslugaComplex_id').setContainerVisible(getRegionNick() != 'kz');

		this.syncSize();
		this.syncShadow();

		this.form.findField('UslugaComplex_id').setAllowedUslugaComplexAttributeList([ 'LuchLech' ]);

		switch (arguments[0].action) {
			case 'add':
				this.setTitle(lang['luchevoe_lechenie_dobavlenie']);
				this.setFieldsDisabled(false);
				break;
			case 'edit':
				this.setTitle(lang['luchevoe_lechenie_redaktirovanie']);
				this.setFieldsDisabled(false);
				break;
			case 'view':
				this.setTitle(lang['luchevoe_lechenie_prosmotr']);
				this.setFieldsDisabled(true);
				break;
		}
		
        var loadMask = new Ext.LoadMask(this.form.getEl(), {msg:lang['zagruzka']});
        loadMask.show();
		switch (arguments[0].action) {
			case 'add':
				thas.form.setValues(arguments[0].formParams);
				thas.InformationPanel.load({
					Person_id: arguments[0].formParams.Person_id
				});
				loadMask.hide();
				thas.setAllowedDates();
				this.AggTypePanel.setValues([null]);
				if ( getRegionNick() != 'kz' ) {
					thas.form.findField('UslugaCategory_id').setFieldValue('UslugaCategory_SysNick', 'gost2011');
					thas.setUslugaComplexFilter();
				}
				thas.setOnkoRadiotherapyFilter();
			break;

			case 'edit':
			case 'view':
				Ext.Ajax.request({
					failure:function () {
						sw.swMsg.alert(lang['oshibka'], lang['ne_udalos_poluchit_dannyie_s_servera']);
						loadMask.hide();
						thas.hide();
					},
					params:{
						EvnUslugaOnkoBeam_id: thas.EvnUslugaOnkoBeam_id
					},
					success: function (response) {
                        loadMask.hide();
						var result = Ext.util.JSON.decode(response.responseText);
						if (result[0]) {
                            thas.form.setValues(result[0]);
                            thas.InformationPanel.load({
                                Person_id: result[0].Person_id
                            });
                            if(result[0].AggTypes){
								thas.AggTypePanel.setValues(result[0].AggTypes);
							} else {
								thas.AggTypePanel.setValues([null]);
							}

							var UslugaComplex_id = thas.form.findField('UslugaComplex_id').getValue();
							thas.setUslugaComplexFilter();

							thas.setOnkoRadiotherapyFilter();

							if ( !Ext.isEmpty(UslugaComplex_id) ) {
								thas.form.findField('UslugaComplex_id').getStore().load({
									callback: function() {
										if ( thas.form.findField('UslugaComplex_id').getStore().getCount() > 0 ) {
											thas.form.findField('UslugaComplex_id').setValue(UslugaComplex_id);
										}
										else {
											thas.form.findField('UslugaComplex_id').clearValue();
										}
									}.createDelegate(this),
									params: {
										UslugaComplex_id: UslugaComplex_id
									}
								});
							}

							thas.setAllowedDates();
                        }
					},
					url:'/?c=EvnUslugaOnkoBeam&m=load'
				});				
			break;	
		}
        return true;
	},
	initComponent: function() {
		var thas = this;

		this.InformationPanel = new sw.Promed.PersonInformationPanelShort({
			region: 'north'
		});

		this.AggTypePanel = new sw.Promed.AddOnkoComplPanel({
			objectName: 'AggType',
			fieldLabelTitle: lang['oslojnenie'],
			win: this,
			width: 780,
			buttonAlign: 'left',
			buttonLeftMargin: 150,
			labelWidth: 200,
			fieldWidth: 400,
			style: 'background: #DFE8F6'
		});

		var form = new Ext.Panel({
			autoHeight: true,
			autoScroll: true,
			bodyBorder: false,
			border: false,
			frame: false,
			region: 'center',
			items: [{
				xtype: 'form',
				autoHeight: true,
				id: 'EvnUslugaOnkoBeamEditForm',
				bodyStyle:'background:#DFE8F6;padding:5px;',
				border: false,
				labelWidth: 200,
				collapsible: true,
				labelAlign: 'right',
				region: 'center',
				url:'/?c=EvnUslugaOnkoBeam&m=save',
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
					bodyStyle: 'background: #DFE8F6;',
					border: false,
					layout: 'column',
					items: [{
						bodyStyle: 'background: #DFE8F6;',
						border: false,
						layout: 'form',
						items: [{
							fieldLabel: langs('Дата начала'),
							name: 'EvnUslugaOnkoBeam_setDate',
					listeners: {
						'change': function(field, newValue) {
							thas.setAllowedDatesForDisField();
							thas.setUslugaComplexFilter();
							thas.setOnkoRadiotherapyFilter();
						}
					},
                    allowBlank: false,
					xtype: 'swdatefield',
					plugins: [new Ext.ux.InputTextMask('99.99.9999', false)]
						}]
				}, {
						bodyStyle: 'background: #DFE8F6;',
						border: false,
						labelWidth: 50,
						layout: 'form',
						items: [{
							allowBlank: false,
							fieldLabel: langs('Время'),
							listeners: {
								'keydown': function (inp, e) {
									if ( e.getKey() == Ext.EventObject.F4 ) {
										e.stopEvent();
										inp.onTriggerClick();
									}
								}
							},
							name: 'EvnUslugaOnkoBeam_setTime',
							onTriggerClick: function() {
								var time_field = thas.form.findField('EvnUslugaOnkoBeam_setTime');

								if ( time_field.disabled ) {
									return false;
								}

								setCurrentDateTime({
									callback: function() {
										thas.form.findField('EvnUslugaOnkoBeam_setDate').fireEvent('change', thas.form.findField('EvnUslugaOnkoBeam_setDate'), thas.form.findField('EvnUslugaOnkoBeam_setDate').getValue());
									},
									dateField: thas.form.findField('EvnUslugaOnkoBeam_setDate'),
									loadMask: true,
									setDate: true,
									setDateMaxValue: false,
									setDateMinValue: false,
									setTime: true,
									timeField: time_field,
									windowId: thas.id
								});
							},	
							plugins: [ new Ext.ux.InputTextMask('99:99', true) ],
							validateOnBlur: false,
							width: 60,
							xtype: 'swtimefield'
						}]
					}]
				}, {
					bodyStyle: 'background: #DFE8F6;',
					border: false,
					layout: 'column',
					items: [{
						bodyStyle: 'background: #DFE8F6;',
						border: false,
						layout: 'form',
						items: [{
							fieldLabel: langs('Дата окончания'),
							name: 'EvnUslugaOnkoBeam_disDate',
					xtype: 'swdatefield',
					plugins: [new Ext.ux.InputTextMask('99.99.9999', false)]
						}]
				}, {
						bodyStyle: 'background: #DFE8F6;',
						border: false,
						labelWidth: 50,
						layout: 'form',
						items: [{
							fieldLabel: langs('Время'),
							listeners: {
								'keydown': function (inp, e) {
									if ( e.getKey() == Ext.EventObject.F4 ) {
										e.stopEvent();
										inp.onTriggerClick();
									}
								}
							},
							name: 'EvnUslugaOnkoBeam_disTime',
							onTriggerClick: function() {
								var time_field = thas.form.findField('EvnUslugaOnkoBeam_disTime');

								if ( time_field.disabled ) {
									return false;
								}

								setCurrentDateTime({
									dateField: thas.form.findField('EvnUslugaOnkoBeam_disDate'),
									loadMask: true,
									setDate: true,
									setDateMaxValue: false,
									setDateMinValue: false,
									setTime: true,
									timeField: time_field,
									windowId: thas.id
								});
							},	
							plugins: [ new Ext.ux.InputTextMask('99:99', true) ],
							validateOnBlur: false,
							width: 60,
							xtype: 'swtimefield'
						}]
					}]
				}, {
					allowBlank: !getRegionNick().inlist([ 'astra', 'kareliya', 'penza', 'perm', 'ufa' ]),
					fieldLabel: langs('Категория услуги'),
					hiddenName: 'UslugaCategory_id',
					listeners: {
						'change': function(combo, newValue, oldValue) {
							var idx = combo.getStore().findBy(function(rec) {
								return rec.get('UslugaCategory_id') == newValue;
							});
							combo.fireEvent('select', combo, combo.getStore().getAt(idx), idx);
						},
						'select': function(combo, record) {
							thas.setUslugaComplexFilter();
						}
					},
					loadParams: {
						params: {
							where: "where UslugaCategory_SysNick in ('gost2011','lpu','tfoms')"
						}
					},
					width: 400,
					xtype: 'swuslugacategorycombo'
				}, {
					allowBlank: !getRegionNick().inlist([ 'astra', 'kareliya', 'penza', 'perm', 'ufa' ]),
					fieldLabel: langs('Название услуги'),
					hiddenName: 'UslugaComplex_id',
					listWidth: 700,
					to: 'EvnUslugaOnkoBeam',
					width: 400,
					xtype: 'swuslugacomplexnewcombo'
				}, {
					fieldLabel: langs('Способ облучения'),
					hiddenName: 'OnkoUslugaBeamIrradiationType_id',
					xtype: 'swcommonsprlikecombo',
					allowBlank: false,
					sortField:'OnkoUslugaBeamIrradiationType_Code',
					comboSubject: 'OnkoUslugaBeamIrradiationType',
					width: 400
				}, {
					fieldLabel: lang['vid_luchevoy_terapii'],
					hiddenName: 'OnkoUslugaBeamKindType_id',
					xtype: 'swcommonsprlikecombo',
					allowBlank: false,
					sortField:'OnkoUslugaBeamKindType_Code',
					comboSubject: 'OnkoUslugaBeamKindType',
					width: 400
				}, {
					fieldLabel: lang['metod_luchevoy_terapii'],
					hiddenName: 'OnkoUslugaBeamMethodType_id',
					xtype: 'swcommonsprlikecombo',
					allowBlank: false,
					sortField:'OnkoUslugaBeamMethodType_Code',
					comboSubject: 'OnkoUslugaBeamMethodType',
					width: 400
				}, {
					fieldLabel: lang['radiomodifikatoryi'],
					hiddenName: 'OnkoUslugaBeamRadioModifType_id',
					xtype: 'swcommonsprlikecombo',
					allowBlank: true,
					sortField:'OnkoUslugaBeamRadioModifType_Code',
					comboSubject: 'OnkoUslugaBeamRadioModifType',
					width: 400
				}, {
                    fieldLabel: lang['preimuschestvennaya_napravlennost_luchevoy_terapii'],
                    hiddenName: 'OnkoUslugaBeamFocusType_id',
                    xtype: 'swcommonsprlikecombo',
                    allowBlank: false,
                    sortField:'OnkoUslugaBeamFocusType_Code',
                    comboSubject: 'OnkoUslugaBeamFocusType',
                    width: 400
                }, {
                    fieldLabel: lang['vid_planirovaniya'],
                    xtype: 'swcommonsprlikecombo',
                    comboSubject: 'OnkoPlanType',
                    width: 400
                }, {
                    fieldLabel: lang['mesto_vyipolneniya'],
                    autoLoad: true,
                    hiddenName: 'Lpu_uid',
                    allowBlank: !getRegionNick().inlist([ 'kareliya', 'perm', 'ufa' ]),
                    xtype: 'swlpulocalcombo',
                    width: 400
				}, {
					comboSubject: 'OnkoTreatType',
					fieldLabel: lang['harakter_lecheniya'],
					hiddenName: 'OnkoTreatType_id',
					sortField:'OnkoTreatType_Code',
					width: 400,
					xtype: 'swcommonsprlikecombo'
				}, {
					allowBlank: false,
					comboSubject: 'OnkoRadiotherapy',
					fieldLabel: langs('Тип лечения'),
					moreFields: [
						{name: 'OnkoRadiotherapy_begDate', type: 'date', dateFormat: 'd.m.Y' },
						{name: 'OnkoRadiotherapy_endDate', type: 'date', dateFormat: 'd.m.Y' }
					],
					width: 300,
					xtype: 'swcommonsprlikecombo'
                }, {
					fieldLabel: langs('Условие проведения лечения'),
					comboSubject: 'TreatmentConditionsType',
					allowBlank: true,
					xtype: 'swcommonsprlikecombo',
					width: 400
                }, 
                this.AggTypePanel, 
                {
					allowBlank: false,
					allowDecimals: false,
					allowNegative: false,
					fieldLabel: langs('Кол-во фракций проведения лучевой терапии'),
					name: 'EvnUslugaOnkoBeam_CountFractionRT',
					width: 80,
					xtype: 'numberfield'
				}, {
					xtype: 'panel',
					layout: 'column',
					labelWidth: 220,
					border: false,
					bodyStyle:'background:#DFE8F6;padding:5px;',
					items: [{
                        layout: 'form',
                        border: false,
                        labelWidth: 380,
                        width: 470,
                        bodyStyle:'background:#DFE8F6;',
                        items: [{
							allowBlank: !getRegionNick().inlist([ 'kareliya', 'penza', 'perm', 'ufa', 'adygeya' ]),
                            fieldLabel: langs('Суммарная доза облучения опухоли'),
                            name: 'EvnUslugaOnkoBeam_TotalDoseTumor',
                            xtype: 'numberfield',
                            tabIndex: TABINDEX_EUCOMEF + 14,
                            autoCreate: {tag: "input", maxLength: "8", autocomplete: "off"},
                            width: 80
                        }, {
                            fieldLabel: lang['summarnaya_doza_oblucheniya_zon_regionarnogo_metastazirovaniya'],
                            name: 'EvnUslugaOnkoBeam_TotalDoseRegZone',
                            xtype: 'numberfield',
                            tabIndex: TABINDEX_EUCOMEF + 16,
                            autoCreate: {tag: "input", maxLength: "8", autocomplete: "off"},
                            width: 80
                        }]
                    }, {
                        layout: 'form',
                        border: false,
                        labelWidth: 1,
                        width: 130,
                        bodyStyle:'background:#DFE8F6;',
                        items: [{
                            hiddenName: 'OnkoUslugaBeamUnitType_id',
                            hideLabel: true,
                            comboSubject: 'OnkoUslugaBeamUnitType',
                            xtype: 'swcommonsprlikecombo',
                            value: 1,
                            tabIndex: TABINDEX_EUCOMEF + 15,
                            width: 120
                        }, {
                            hiddenName: 'OnkoUslugaBeamUnitType_did',
                            hideLabel: true,
                            comboSubject: 'OnkoUslugaBeamUnitType',
                            xtype: 'swcommonsprlikecombo',
                            value: 1,
                            tabIndex: TABINDEX_EUCOMEF + 17,
                            width: 120
                        }]
					}]
				}]
			}],
			reader: new Ext.data.JsonReader({
				success: Ext.emptyFn
			}, [
				{name: 'EvnUslugaOnkoBeam_pid'},
				{name: 'Server_id'},
                {name: 'PersonEvn_id'},
                {name: 'Person_id'},
				{name: 'EvnUslugaOnkoBeam_setDate'}, 
				{name: 'EvnUslugaOnkoBeam_setTime'}, 
				{name: 'EvnUslugaOnkoBeam_disDate'},
				{name: 'EvnUslugaOnkoBeam_disTime'},
				{name: 'Morbus_id'},
				{name: 'Lpu_uid'},
				{name: 'EvnUslugaOnkoBeam_id'}, 
				{name: 'OnkoUslugaBeamIrradiationType_id'}, 
				{name: 'OnkoUslugaBeamKindType_id'}, 
				{name: 'OnkoUslugaBeamMethodType_id'}, 
				{name: 'OnkoUslugaBeamRadioModifType_id'}, 
				{name: 'OnkoUslugaBeamFocusType_id'},
                {name: 'OnkoPlanType_id'},
                {name: 'AggType_id'},
                {name: 'OnkoTreatType_id'},
                {name: 'OnkoRadiotherapy_id'},
                {name: 'TreatmentConditionsType_id'},
                {name: 'EvnUslugaOnkoBeam_CountFractionRT'},
                {name: 'EvnUslugaOnkoBeam_TotalDoseTumor'},
                {name: 'OnkoUslugaBeamUnitType_id'},
                {name: 'EvnUslugaOnkoBeam_TotalDoseRegZone'},
				{name: 'OnkoUslugaBeamUnitType_did'},
				{name: 'UslugaCategory_id'},
				{name: 'UslugaComplex_id'}
			]),
			url: '/?c=EvnUslugaOnkoBeam&m=save'
		});
		Ext.apply(this, {
			buttons:
			[{
				handler: function() 
				{
					this.ownerCt.doSave();
				},
				iconCls: 'save16',
				text: BTN_FRMSAVE
			}, 
			{
				text: '-'
			},
			HelpButton(this, 0),//todo проставить табиндексы
			{
				handler: function() 
				{
					this.ownerCt.hide();
				},
				iconCls: 'cancel16',
				text: BTN_FRMCANCEL
			}],
			items:[this.InformationPanel,form]
		});
		sw.Promed.swEvnUslugaOnkoBeamEditWindow.superclass.initComponent.apply(this, arguments);
		this.form = this.findById('EvnUslugaOnkoBeamEditForm').getForm();
	}	
});
