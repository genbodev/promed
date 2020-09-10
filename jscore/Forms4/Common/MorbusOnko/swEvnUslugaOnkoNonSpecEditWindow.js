/**
 * swEvnUslugaOnkoNonSpecEditWindow - окно редактирования "Неспецифическое лечение"
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

Ext6.define('common.MorbusOnko.swEvnUslugaOnkoNonSpecEditWindow', {
	/* свойства */
	requires: [
		'common.EMK.PersonInfoPanelShort',
	],
	alias: 'widget.swEvnUslugaOnkoNonSpecEditWindow',
    autoShow: false,
	closable: true,
	cls: 'arm-window-new emkd',
	constrain: true,
	extend: 'base.BaseForm',
	findWindow: false,
    header: true,
	modal: true,
	layout: 'form',
	refId: 'EvnUslugaOnkoNonSpeceditsw',
	renderTo: Ext.getCmp('main-center-panel').body.dom,
	resizable: false,
	title: 'Неспецифическое лечение',
	width: 700,

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

		base_form.submit({
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
	onSprLoad: function(arguments) {

		var win = this;
		var base_form = win.FormPanel.getForm();

		this.action = '';
		this.callback = Ext.emptyFn;
		this.EvnUslugaOnkoNonSpec_id = null;

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

		if ( arguments[0].EvnUslugaOnkoNonSpec_id ) {
			this.EvnUslugaOnkoNonSpec_id = arguments[0].EvnUslugaOnkoNonSpec_id;
		}

		this.MorbusOnkoVizitPLDop_id = arguments[0].MorbusOnkoVizitPLDop_id || null;
		this.MorbusOnkoLeave_id = arguments[0].MorbusOnkoLeave_id || null;
		this.MorbusOnkoDiagPLStom_id = arguments[0].MorbusOnkoDiagPLStom_id || null;

		base_form.reset();

		base_form.findField('UslugaComplex_id').setDisallowedUslugaComplexAttributeList([ 'LuchLech', 'XimLech', 'GormImunTerLech', 'XirurgLech' ]);
		base_form.findField('UslugaComplex_id').getStore().proxy.extraParams.EvnUsluga_pid = null;
		base_form.findField('UslugaComplex_id').getStore().proxy.extraParams.UslugaComplex_Date = null;
		
		base_form.findField('UslugaCategory_id').getStore().filterBy(function(rec) {
			return rec.get('UslugaCategory_SysNick').inlist(['gost2011','lpu','tfoms']);
		});
		
		win.mask(LOAD_WAIT);
		
		win.PersonInfoPanel.load({
			Person_id: arguments[0].formParams.Person_id
		});

		switch ( win.action ) {
			case 'add':
				win.setTitle('Неспецифическое лечение: Добавление');
				base_form.setValues(arguments[0].formParams);
				win.unmask();
				base_form.findField('UslugaCategory_id').setFieldValue('UslugaCategory_SysNick', 'gost2011');
				win.setAllowedDates();
				base_form.findField('Lpu_Name').setValue(getGlobalOptions().lpu_nick);
				win.setUslugaComplexFilter();
				base_form.isValid();
				break;

			case 'edit':
			case 'view':
				win.setTitle('Неспецифическое лечение: Редактирование');
				Ext.Ajax.request({
					failure:function () {
						sw.swMsg.alert(langs('Ошибка'), langs('Не удалось получить данные с сервера'));
						win.unmask();
						win.hide();
					},
					params:{
						EvnUslugaOnkoNonSpec_id: win.EvnUslugaOnkoNonSpec_id
					},
					success: function (response) {
						var result = Ext.util.JSON.decode(response.responseText);
						if (result[0]) {
							base_form.setValues(result[0]);
							win.unmask();

							var UslugaComplex_id = result[0].UslugaComplex_id || null;
							
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
					url:'/?c=EvnUslugaOnkoNonSpec&m=load'
				});
				break;
		}
	},

	show: function() {
		this.callParent(arguments);
	},
	
	setAllowedDates: function() {
		var win = this;
		var base_form = win.FormPanel.getForm();
		var set_dt_field = base_form.findField('EvnUslugaOnkoNonSpec_setDT');
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
	
	setUslugaComplexFilter: function() {
		var
			base_form = this.FormPanel.getForm(),
			EvnUsluga_pid = base_form.findField('EvnUslugaOnkoNonSpec_pid').getValue(),
			UslugaCategory_SysNick = base_form.findField('UslugaCategory_id').getFieldValue('UslugaCategory_SysNick'),
			UslugaComplex_Date = base_form.findField('EvnUslugaOnkoNonSpec_setDT').getValue();

		base_form.findField('UslugaComplex_id').setAllowBlank(Ext.isEmpty(UslugaCategory_SysNick));
		base_form.findField('UslugaComplex_id').setContainerVisible(!Ext.isEmpty(UslugaCategory_SysNick));

		if (
			(
				Ext.isEmpty(UslugaCategory_SysNick)
				|| base_form.findField('UslugaComplex_id').getStore().proxy.extraParams.uslugaCategoryList == Ext.util.JSON.encode([ UslugaCategory_SysNick ])
			)
			&& (
				typeof UslugaComplex_Date != 'object'
				|| base_form.findField('UslugaComplex_id').getStore().proxy.extraParams.UslugaComplex_Date == Ext.util.Format.date(UslugaComplex_Date, 'd.m.Y')
			)
			&& base_form.findField('UslugaComplex_id').getStore().proxy.extraParams.EvnUsluga_pid == EvnUsluga_pid
		) {
			return false;
		}

		base_form.findField('UslugaComplex_id').clearValue();
		base_form.findField('UslugaComplex_id').getStore().removeAll();
		base_form.findField('UslugaComplex_id').lastQuery = 'This query sample that is not will never appear';

		base_form.findField('UslugaComplex_id').getStore().proxy.extraParams.EvnUsluga_pid = EvnUsluga_pid;
		base_form.findField('UslugaComplex_id').getStore().proxy.extraParams.UslugaComplex_Date = Ext.util.Format.date(UslugaComplex_Date, 'd.m.Y');
		base_form.findField('UslugaComplex_id').setUslugaCategoryList([ UslugaCategory_SysNick ]);
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

		win.FormPanel = new Ext6.form.FormPanel({
			autoScroll: true,
			border: false,
			bodyPadding: '15 25 15 37',
			defaults: {
				labelAlign: 'left',
				labelWidth: 150
			},
			reader: Ext6.create('Ext6.data.reader.Json', {
				type: 'json',
				model: win.id + '_FormModel'
			}),
			url: '/?c=EvnUslugaOnkoNonSpec&m=save',
			items: [{
				name: 'EvnUslugaOnkoNonSpec_id',
				xtype: 'hidden'
			}, {
				name: 'EvnUslugaOnkoNonSpec_pid',
				xtype: 'hidden'
			}, {
				name: 'MorbusOnko_id',
				xtype: 'hidden'
			}, {
				name: 'Morbus_id',
				xtype: 'hidden'
			}, {
				name: 'EvnUsluga_id',
				xtype: 'hidden'
			}, {
				name: 'Server_id',
				xtype: 'hidden'
			}, {
				name: 'Person_id',
				xtype: 'hidden'
			}, {
				name: 'PersonEvn_id',
				xtype: 'hidden'
			}, {
				disabled: true,
				fieldLabel: langs('МО'),
				name: 'Lpu_Name',
				readOnly: true,
				width: 600,
				xtype: 'textfield'
			}, {
				fieldLabel: langs('Дата'),
				name: 'EvnUslugaOnkoNonSpec_setDT',
				listeners: {
					'change': function(field, newValue) {
						win.setUslugaComplexFilter();
					}
				},
				allowBlank: false,
				xtype: 'datefield'
			}, {
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
				width: 600,
				xtype: 'commonSprCombo'
			}, {
				fieldLabel: langs('Услуга'),
				name: 'UslugaComplex_id',
				listeners: {
					'change': function(combo, newValue, oldValue) {
						var idx = combo.getStore().findBy(function(rec) {
							return rec.get(combo.valueField) == newValue;
						});
						combo.fireEvent('select', combo, combo.getStore().getAt(idx), idx);
					},
					'select': function(combo, record) {
						if ( typeof record == 'object' && !Ext.isEmpty(record.get('EvnUsluga_setDate')) ) {
							base_form.findField('EvnUslugaOnkoNonSpec_setDT').setValue(record.get('EvnUsluga_setDate').format('d.m.Y'));
						}
					}
				},
				listWidth: 700,
				to: 'EvnUslugaOnkoNonSpec',
				useEvnUslugaData: true,
				width: 600,
				xtype: 'swUslugaComplexCombo'
			}]
		});

        Ext6.apply(win, {
			items: [
				win.PersonInfoPanel,
				win.FormPanel
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