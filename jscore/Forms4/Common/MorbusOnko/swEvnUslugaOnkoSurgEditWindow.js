/**
 * swEvnUslugaOnkoSurgEditWindow - окно редактирования "Хирургическое лечение"
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common.MorbusOnko
 * @access       public
 * @copyright    Copyright (c) 2013 Swan Ltd.
 * @version      2019
 * @comment
 */

Ext6.define('common.MorbusOnko.swEvnUslugaOnkoSurgEditWindow', {
	/* свойства */
	requires: [
		'common.MorbusOnko.AddOnkoComplPanel',
		'common.EMK.PersonInfoPanelShort',
	],
	alias: 'widget.swEvnUslugaOnkoSurgEditWindow',
    autoShow: false,
	closable: true,
	cls: 'arm-window-new emkd',
	constrain: true,
	extend: 'base.BaseForm',
	findWindow: false,
    header: true,
	modal: true,
	layout: 'form',
	refId: 'EvnUslugaOnkoSurgeditsw',
	renderTo: Ext.getCmp('main-center-panel').body.dom,
	resizable: false,
	title: 'Хирургическое лечение',
	width: 820,
	maxHeight: main_center_panel.body.getHeight() - 50,

	/* методы */
	save: function () {
		var
			base_form = this.FormPanel.getForm(),
			win = this;

		if ( !base_form.isValid() ) {
			sw.swMsg.alert('Ошибка', 'Не все поля формы заполнены корректно');
			return false;
		}
		
        var params = {};
        params.action = win.action;
		if (this.EvnPL_id)
			params.EvnPL_id = this.EvnPL_id;
        var AggTypes = this.AggTypePanel.getValues();
        params.AggTypes = (AggTypes.length > 1 ? AggTypes.join(',') : AggTypes);
        var AggTypes2 = this.AggTypePanel2.getValues();
        params.AggTypes2 = (AggTypes2.length > 1 ? AggTypes2.join(',') : AggTypes2);

		win.mask(LOAD_WAIT_SAVE);

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
	setAllowedDates: function() {
		var win = this;
		var base_form = this.FormPanel.getForm();
		var set_dt_field = base_form.findField('EvnUslugaOnkoSurg_setDate');
		var morbus_id = base_form.findField('Morbus_id').getValue();
        var morbusonkovizitpldop_id = win.MorbusOnkoVizitPLDop_id;
        var morbusonkoleave_id = win.MorbusOnkoLeave_id;
        var morbusonkodiagplstom_id = win.MorbusOnkoDiagPLStom_id;
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
					if (result[0] && Ext.isArray(result[0].disabledDates) && result[0].disabledDates.length > 0) {
						//set_dt_field.setAllowedDates(result[0].disabledDates);
					} else {
						//set_dt_field.setAllowedDates(null);
					}
				},
				url:'/?c=MorbusOnkoSpecifics&m=getMorbusOnkoSpecTreatDisabledDates'
			});
		} else {
			//set_dt_field.setAllowedDates(null);
		}
	},
	setUslugaFieldsParams: function(loadOnEdit) {
		var
			base_form = this.FormPanel.getForm(),
			dateX20180101 = new Date(2018, 0, 1),
			dateX20180701 = new Date(2018, 6, 1),
			dateX20180901 = new Date(2018, 8, 1),
			UslugaComplex_Date = base_form.findField('EvnUslugaOnkoSurg_setDate').getValue();

		if ( getRegionNick == 'kz' ) {
			base_form.findField('UslugaCategory_id').disable();
			base_form.findField('UslugaComplex_id').setAllowedUslugaComplexAttributeList([ 'oper' ]);

			base_form.findField('UslugaCategory_id').getStore().clearFilter();
			base_form.findField('UslugaCategory_id').lastQuery = '';
			base_form.findField('UslugaCategory_id').getStore().filterBy(function(rec) {
				return (!Ext.isEmpty(rec.get('UslugaCategory_SysNick')) && rec.get('UslugaCategory_SysNick').inlist([ 'classmedus' ]));
			});

			if ( base_form.findField('UslugaCategory_id').getStore().getCount() == 1 ) {
				base_form.findField('UslugaCategory_id').setValue(base_form.findField('UslugaCategory_id').getStore().getAt(0).get('UslugaCategory_id'));
			}
		}
		else if (
			typeof UslugaComplex_Date == 'object'
			&& (
				(getRegionNick().inlist([ 'perm' ]) && UslugaComplex_Date >= dateX20180101)
				|| (getRegionNick().inlist([ 'astra', 'kareliya', 'penza' ]) && UslugaComplex_Date >= dateX20180701)
				|| (!getRegionNick().inlist([ 'astra', 'kareliya', 'penza', 'perm' ]) && UslugaComplex_Date >= dateX20180901)
			)
		) {
			if ( this.action != 'view' ) {
				base_form.findField('UslugaCategory_id').enable();
			}

			base_form.findField('UslugaComplex_id').setAllowedUslugaComplexAttributeList([ 'XirurgLech' ]);

			base_form.findField('UslugaCategory_id').getStore().clearFilter();
			base_form.findField('UslugaCategory_id').lastQuery = '';
			base_form.findField('UslugaCategory_id').getStore().filterBy(function(rec) {
				return (!Ext.isEmpty(rec.get('UslugaCategory_SysNick')) && rec.get('UslugaCategory_SysNick').inlist([ 'gost2011', 'lpu', 'tfoms' ]));
			});

			if ( base_form.findField('UslugaCategory_id').listMode != 2 && !loadOnEdit ) {
				base_form.findField('UslugaCategory_id').clearValue();
			}

			if ( Ext.isEmpty(base_form.findField('UslugaCategory_id').getValue()) ) {
				base_form.findField('UslugaCategory_id').setFieldValue('UslugaCategory_SysNick', 'gost2011');
			}

			base_form.findField('UslugaCategory_id').listMode = 2;
		}
		else {
			var list = new Array();

			base_form.findField('UslugaCategory_id').disable();
			base_form.findField('UslugaComplex_id').setAllowedUslugaComplexAttributeList([ 'oper' ]);

			list.push('gost2011');

			base_form.findField('UslugaCategory_id').getStore().clearFilter();
			base_form.findField('UslugaCategory_id').lastQuery = '';
			base_form.findField('UslugaCategory_id').getStore().filterBy(function(rec) {
				return (!Ext.isEmpty(rec.get('UslugaCategory_SysNick')) && rec.get('UslugaCategory_SysNick').inlist(list));
			});

			if ( base_form.findField('UslugaCategory_id').getStore().getCount() == 1 ) {
				base_form.findField('UslugaCategory_id').setValue(base_form.findField('UslugaCategory_id').getStore().getAt(0).get('UslugaCategory_id'));
			}

			base_form.findField('UslugaCategory_id').listMode = 1;
		}
	},
	setUslugaComplexFilter: function() {
		var
			base_form = this.FormPanel.getForm(),
			Lpu_uid = base_form.findField('Lpu_uid').getValue(),
			UslugaCategory_SysNick = base_form.findField('UslugaCategory_id').getFieldValue('UslugaCategory_SysNick'),
			UslugaComplex_Date = base_form.findField('EvnUslugaOnkoSurg_setDate').getValue();

		if (
			(
				Ext.isEmpty(UslugaCategory_SysNick)
				|| base_form.findField('UslugaComplex_id').getStore().proxy.extraParams.uslugaCategoryList == Ext.util.JSON.encode([ UslugaCategory_SysNick ])
			)
			&& (
				base_form.findField('UslugaComplex_id').getStore().proxy.extraParams.Lpu_uid == Lpu_uid
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
		base_form.findField('UslugaComplex_id').lastQuery = 'This query sample that is not will never appear';

		base_form.findField('UslugaComplex_id').getStore().proxy.extraParams.Lpu_uid = Lpu_uid;
		base_form.findField('UslugaComplex_id').getStore().proxy.extraParams.UslugaComplex_Date = Ext.util.Format.date(UslugaComplex_Date, 'd.m.Y');
		base_form.findField('UslugaComplex_id').setUslugaCategoryList([ UslugaCategory_SysNick ]);
	},
	onSprLoad: function(arguments) {

		var win = this;

		win.action = (typeof arguments[0].action == 'string' ? arguments[0].action : 'add');
		win.callback = (typeof arguments[0].callback == 'function' ? arguments[0].callback : Ext6.emptyFn);
		win.formParams = (typeof arguments[0].formParams == 'object' ? arguments[0].formParams : {});

		win.center();
		win.setTitle('Хирургическое лечение');
		
		if (arguments[0]['EvnUslugaOnkoSurg_id']) {
			this.EvnUslugaOnkoSurg_id = arguments[0]['EvnUslugaOnkoSurg_id'];
		} else {
			this.EvnUslugaOnkoSurg_id = null;
		}
		
		if (arguments[0]['MorbusOnko_id']) {
			this.MorbusOnko_id = arguments[0]['MorbusOnko_id'];
		} else {
			this.MorbusOnko_id = null;
		}
		
		if (arguments[0]['MorbusOnkoVizitPLDop_id']) {
			this.MorbusOnkoVizitPLDop_id = arguments[0]['MorbusOnkoVizitPLDop_id'];
		} else {
			this.MorbusOnkoVizitPLDop_id = null;
		}
		
		if (arguments[0]['MorbusOnkoDiagPLStom_id']) {
			this.MorbusOnkoDiagPLStom_id = arguments[0]['MorbusOnkoDiagPLStom_id'];
		} else {
			this.MorbusOnkoDiagPLStom_id = null;
		}
		
		if (arguments[0]['MorbusOnkoLeave_id']) {
			this.MorbusOnkoLeave_id = arguments[0]['MorbusOnkoLeave_id'];
		} else {
			this.MorbusOnkoLeave_id = null;
		}
		
		if (arguments[0]['EvnVizitPL_id']) {
			this.EvnVizitPL_id = arguments[0]['EvnVizitPL_id'];
		}

		if(arguments[0]['EvnSection_id']) {
			this.EvnSection_id = arguments[0]['EvnSection_id'];
		} else {
			this.EvnSection_id = null;
		}
		
        if (arguments[0].formParams.EvnUslugaOnkoSurg_pid) {
            this.EvnUslugaOnkoSurg_pid = arguments[0].formParams.EvnUslugaOnkoSurg_pid;
        }

		var base_form = win.FormPanel.getForm();
		base_form.reset();
		base_form.setValues(win.formParams);
		
		win.PersonInfoPanel.load({
			Person_id: arguments[0].formParams.Person_id
		});
		
		var params = new Object();

		if ( !Ext.isEmpty(arguments[0].formParams.MorbusOnkoLeave_id) ) {
			params.EvnSection_id = win.EvnUslugaOnkoSurg_pid;
		}
		else if ( !Ext.isEmpty(arguments[0].formParams.MorbusOnkoVizitPLDop_id) ) {
			params.EvnVizitPL_id = win.EvnUslugaOnkoSurg_pid;
		}
		else if ( !Ext.isEmpty(arguments[0].formParams.MorbusOnkoDiagPLStom_id) ) {
			params.EvnDiagPLStom_id = win.EvnUslugaOnkoSurg_pid;
		}

        //фильтруем поле "Тип лечения" по дате окончания случая лечения, либо по текущей дате если лечение не окончено.
        Ext6.Ajax.request({
            url: '/?c=EvnPL&m=getLastVizitDT',
            params: params,
            success: function (response) {
                var result = Ext.util.JSON.decode(response.responseText);
                var endTreatDate = getValidDT(!Ext.isEmpty(result.endTreatDate) ? result.endTreatDate : getGlobalOptions().date, '');

                base_form.findField('OnkoSurgicalType_id').getStore().clearFilter();
                base_form.findField('OnkoSurgicalType_id').lastQuery = '';	
                base_form.findField('OnkoSurgicalType_id').getStore().filterBy(function(rec) {
                    return (	
                        (Ext.isEmpty(rec.get('OnkoSurgicalType_begDate')) || rec.get('OnkoSurgicalType_begDate') <= endTreatDate)
                        && (Ext.isEmpty(rec.get('OnkoSurgicalType_endDate')) || rec.get('OnkoSurgicalType_endDate') >= endTreatDate)
                    );
                });
                base_form.findField('OnkoSurgicalType_id').baseFilterFn = function(rec) {
                    return (	
                        (Ext.isEmpty(rec.get('OnkoSurgicalType_begDate')) || rec.get('OnkoSurgicalType_begDate') <= endTreatDate)
                        && (Ext.isEmpty(rec.get('OnkoSurgicalType_endDate')) || rec.get('OnkoSurgicalType_endDate') >= endTreatDate)
                    );
                };
            }
        });

		switch ( win.action ) {
			case 'add':
				win.setTitle(win.getTitle() + ': Добавление');
				win.setUslugaFieldsParams(true);
				win.setUslugaComplexFilter();
                if (arguments[0].formParams.EvnUslugaOnkoSurg_pid) {
                    Ext6.Ajax.request({
                        failure:function () {
							win.setAllowedDates();
                        },
                        params:{
                            EvnUslugaOnkoSurg_pid: arguments[0].formParams.EvnUslugaOnkoSurg_pid
                        },
                        success: function (response) {
							win.setAllowedDates();
                            var result = Ext.util.JSON.decode(response.responseText);
                            if (result.success && result.TreatmentConditionsType_id) {
                                base_form.findField('TreatmentConditionsType_id').setValue(result.TreatmentConditionsType_id);
                            }
                        },
                        url:'/?c=EvnUslugaOnkoSurg&m=getDefaultTreatmentConditionsTypeId'
                    });
                } else {
					win.setAllowedDates();
                }
                win.AggTypePanel.setValues([null]);
                win.AggTypePanel2.setValues([null]);
				base_form.findField('EvnUslugaOnkoSurg_setDate').focus();
				base_form.isValid();
				break;

			case 'edit':
				win.setTitle(win.getTitle() + ': Редактирование');

				win.mask(LOAD_WAIT);

				base_form.load({
					url: '/?c=EvnUslugaOnkoSurg&m=load',
					params: {
						EvnUslugaOnkoSurg_id: base_form.findField('EvnUslugaOnkoSurg_id').getValue()
					},
					success: function(form, action) {
						win.unmask();
						var result = Ext.util.JSON.decode(action.response.responseText);
						if (result[0]) {
							base_form.findField('EvnUslugaOnkoSurg_setDate').focus();
							win.AggTypePanel.setValues(result[0].AggTypes);
							win.AggTypePanel2.setValues(result[0].AggTypes2);
							var UslugaComplex_id = result[0].UslugaComplex_id || null;
							if ( !Ext.isEmpty(UslugaComplex_id) ) {
							win.mask(LOAD_WAIT);
								base_form.findField('UslugaComplex_id').getStore().load({
									callback: function() {
										win.unmask();
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
							base_form.isValid();
						}
					},
					failure: function() {
						win.unmask();
					}
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
            fieldLabelTitle: langs('Интраоперационное осложнение'),
			win: this,
			width: 740,
			buttonAlign: 'left',
			buttonLeftMargin: 150,
			fieldWidth: 700,
			labelWidth: 240
		});
		
		win.AggTypePanel2 = Ext6.create('common.MorbusOnko.AddOnkoComplPanel', {
			objectName: 'AggType',
            fieldLabelTitle: langs('Послеоперационное осложнение'),
			win: this,
			width: 740,
			buttonAlign: 'left',
			buttonLeftMargin: 150,
			fieldWidth: 700,
			labelWidth: 240
		});

		win.FormPanel = new Ext6.form.FormPanel({
			autoScroll: true,
			border: false,
			cls: 'emk_forms accordion-panel-window',
			bodyPadding: '15 25 15 37',
			defaults: {
				labelAlign: 'left',
				labelWidth: 240
			},
			reader: Ext6.create('Ext6.data.reader.Json', {
				type: 'json',
				model: win.id + '_FormModel'
			}),
			url: '/?c=EvnUslugaOnkoSurg&m=save',
			items: [{
				name: 'EvnUslugaOnkoSurg_id',
				xtype: 'hidden',
				value: 0
			}, {
				name: 'EvnUslugaOnkoSurg_pid',
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
					labelWidth: 240
				},
				layout: 'column',
				columns: [0.4, 0.4],
				items: [{
					fieldLabel: langs('Дата проведения'),
					name: 'EvnUslugaOnkoSurg_setDate',
					allowBlank: false,
					xtype: 'swDateField',
					listeners: {
						'change': function(field){
						   win.setUslugaFieldsParams();
						   win.setUslugaComplexFilter();
						}
					},
				}, {
					labelAlign: 'right',
					labelWidth: 80,
					allowBlank: false,
					fieldLabel: langs('Время'),
					name: 'EvnUslugaOnkoSurg_setTime',
					width: 200,
					xtype: 'swTimeField'
				}]
			}, {
				fieldLabel: langs('Место выполнения'),
				autoLoad: true,
				name: 'Lpu_uid',
				allowBlank: !getRegionNick().inlist([ 'kareliya', 'perm', 'ufa' ]),
				xtype: 'swLpuCombo',
				width: 700,
				listeners: {
					change: function(combo, newValue){
						var base_form = win.FormPanel.getForm();
						var mp_combo = base_form.findField('MedPersonal_id');
						var mp_combo_value = mp_combo.getValue();
						var on_date = '';
						var set_dt = base_form.findField('EvnUslugaOnkoSurg_setDate').getValue();
						if (set_dt) {
							on_date = Ext.util.Format.date(set_dt, 'd.m.Y');
						}

						win.setUslugaComplexFilter();

						mp_combo.lastQuery = '';
						mp_combo.clearValue();
						mp_combo.getStore().removeAll();
						mp_combo.getStore().load({
							params: {
								Lpu_id: newValue,
								onDate: on_date
							},
							callback: function() {
								var index = mp_combo.getStore().findBy(function(record) {
									return ( record.get('MedPersonal_id') == mp_combo_value );
								}.createDelegate(this));
								var record = mp_combo.getStore().getAt(index);
								if ( record ) {
									mp_combo.setValue(mp_combo_value);
									mp_combo.fireEvent('change', mp_combo, mp_combo_value, null);
								}
								else {
									mp_combo.clearValue();
									mp_combo.fireEvent('change', mp_combo, null);
								}
							}
						});
					}
				}
			}, {
				allowBlank: false,
				allowSysNick: true,
				changeDisabled: (getRegionNick().inlist(['ufa'])),
				comboSubject: 'UslugaCategory',
				fieldLabel: langs('Категория услуги'),
				name: 'UslugaCategory_id',
				lastQuery: '',
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
				allowBlank: false,
				fieldLabel: langs('Название операции'),
				name: 'UslugaComplex_id',
				to: 'EvnUslugaOnkoSurg',
				listWidth: 500,
				width: 700,
				xtype: 'swUslugaComplexCombo'
			},{
				fieldLabel: langs('Тип операции'),
				name: 'OperType_id',
				xtype: 'commonSprCombo',
				allowBlank: true,
				sortField:'OperType_Code',
				comboSubject: 'OperType',
				width: 700
			}, {
				fieldLabel: langs('Кто проводил'),
				autoLoad: false,
				ctxSerach: true,
				editable: true,
				name: 'MedPersonal_id',
				xtype: 'SwMedStaffFactGlobalCombo',
				valueField: 'MedPersonal_id',
				allowBlank: true,
				anchor: null,
				width: 700
			}, {
				fieldLabel: langs('Условие проведения лечения'),
				comboSubject: 'TreatmentConditionsType',
				name: 'TreatmentConditionsType_id',
				allowBlank: true,
				xtype: 'commonSprCombo',
				width: 700
			}, {
				fieldLabel: langs('Характер хирургического лечения'),
				name: 'OnkoSurgTreatType_id',
				xtype: 'commonSprCombo',
				sortField:'OnkoSurgTreatType_Code',
				comboSubject: 'OnkoSurgTreatType',
				width: 700
			}, {
				allowBlank: false,
				comboSubject: 'OnkoSurgicalType',
				fieldLabel: langs('Тип лечения'),
				name: 'OnkoSurgicalType_id',
				moreFields: [
					{name: 'OnkoSurgicalType_begDate', type: 'date', dateFormat: 'd.m.Y' },
					{name: 'OnkoSurgicalType_endDate', type: 'date', dateFormat: 'd.m.Y' }
				],
				width: 700,
				xtype: 'commonSprCombo'
			},
			win.AggTypePanel,
			win.AggTypePanel2
			]
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
