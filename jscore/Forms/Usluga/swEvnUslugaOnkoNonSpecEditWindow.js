/**
 * swEvnUslugaOnkoNonSpecEditWindow - окно редактирования "Неспецифическое лечение"
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      MorbusOnko
 * @access       public
 * @copyright    Copyright (c) 2018 Swan Ltd.
 * @version      12.2018
 * @comment
 */
sw.Promed.swEvnUslugaOnkoNonSpecEditWindow = Ext.extend(sw.Promed.BaseForm, {
	/* свойства */
	action: null,
	autoHeight: true,
	buttonAlign: 'left',
	closable: true,
	closeAction: 'hide',
	disabledDatePeriods: null,
	draggable: true,
	formMode: 'remote',
	formStatus: 'edit',
	layout: 'form',
	listeners: {
		hide: function() {
			this.onHide();
		}
	},
	modal: true,
	width: 750,
	winTitle: langs('Неспецифическое лечение'),

	/* методы */
	callback: Ext.emptyFn,
	doSave:  function(options) {
		var win = this;

		if ( !this.form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					win.findById('EvnUslugaOnkoNonSpecEditForm').getFirstInvalidEl().focus(true);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		this.submit();

		return true;
	},
	onHide: Ext.emptyFn,
	setAllowedDates: function() {
		var that = this;
		var set_dt_field = that.form.findField('EvnUslugaOnkoNonSpec_setDT');
		var morbus_id = that.form.findField('Morbus_id').getValue();
		var morbusonkovizitpldop_id = that.MorbusOnkoVizitPLDop_id;
		var morbusonkoleave_id = that.MorbusOnkoLeave_id;
		var morbusonkodiagplstom_id = that.MorbusOnkoDiagPLStom_id;

		that.disabledDatePeriods = null;

		if (morbus_id) {
			var loadMask = new Ext.LoadMask(this.form.getEl(), {msg:langs('Загрузка...')});
			loadMask.show();
			Ext.Ajax.request({
				failure:function () {
					sw.swMsg.alert(langs('Ошибка'), langs('Не удалось получить данные с сервера'));
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
					} else {
						set_dt_field.setAllowedDates(null);
					}
				},
				url:'/?c=MorbusOnkoSpecifics&m=getMorbusOnkoSpecTreatDisabledDates'
			});
		} else {
			set_dt_field.setAllowedDates(null);
		}
	},
	setFieldsDisabled: function(d) {
		var form = this;
		this.form.items.each(function(f) {
			if (f && (f.xtype != 'hidden') && (f.xtype != 'fieldset')  && (f.changeDisabled !== false)) {
				f.setDisabled(d);
			}
		});
		form.buttons[0].setDisabled(d);
	},
	setUslugaComplexFilter: function() {
		var
			base_form = this.form,
			EvnUsluga_pid = base_form.findField('EvnUslugaOnkoNonSpec_pid').getValue(),
			UslugaCategory_SysNick = base_form.findField('UslugaCategory_id').getFieldValue('UslugaCategory_SysNick'),
			UslugaComplex_Date = base_form.findField('EvnUslugaOnkoNonSpec_setDT').getValue();

		base_form.findField('UslugaComplex_id').setAllowBlank(Ext.isEmpty(UslugaCategory_SysNick));
		base_form.findField('UslugaComplex_id').setContainerVisible(!Ext.isEmpty(UslugaCategory_SysNick));

		this.syncSize();
		this.syncShadow();

		if (
			(
				Ext.isEmpty(UslugaCategory_SysNick)
				|| base_form.findField('UslugaComplex_id').getStore().baseParams.uslugaCategoryList == Ext.util.JSON.encode([ UslugaCategory_SysNick ])
			)
			&& (
				typeof UslugaComplex_Date != 'object'
				|| base_form.findField('UslugaComplex_id').getStore().baseParams.UslugaComplex_Date == Ext.util.Format.date(UslugaComplex_Date, 'd.m.Y')
			)
			&& base_form.findField('UslugaComplex_id').getStore().baseParams.EvnUsluga_pid == EvnUsluga_pid
		) {
			return false;
		}

		base_form.findField('UslugaComplex_id').clearValue();
		base_form.findField('UslugaComplex_id').getStore().removeAll();
		base_form.findField('UslugaComplex_id').lastQuery = 'This query sample that is not will never appear';

		base_form.findField('UslugaComplex_id').getStore().baseParams.EvnUsluga_pid = EvnUsluga_pid;
		base_form.findField('UslugaComplex_id').getStore().baseParams.UslugaComplex_Date = Ext.util.Format.date(UslugaComplex_Date, 'd.m.Y');
		base_form.findField('UslugaComplex_id').setUslugaCategoryList([ UslugaCategory_SysNick ]);
	},
	show: function() {
		sw.Promed.swEvnUslugaOnkoNonSpecEditWindow.superclass.show.apply(this, arguments);

		var win = this;

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

		this.form.reset();

		var DisallowedUslugaComplexAttributeList = [ 'LuchLech', 'XimLech', 'GormImunTerLech', 'XirurgLech' ];

		if ( getRegionNick() == 'kareliya' ) {
			DisallowedUslugaComplexAttributeList.push('lab');
			DisallowedUslugaComplexAttributeList.push('func');
		}

		this.form.findField('UslugaComplex_id').setDisallowedUslugaComplexAttributeList(DisallowedUslugaComplexAttributeList);
		this.form.findField('UslugaComplex_id').getStore().baseParams.EvnUsluga_pid = null;
		this.form.findField('UslugaComplex_id').getStore().baseParams.UslugaComplex_Date = null;

		switch (arguments[0].action) {
			case 'add':
				this.setTitle(this.winTitle + langs(': Добавление'));
				this.setFieldsDisabled(false);
				break;
			case 'edit':
				this.setTitle(this.winTitle + langs(': Редактирование'));
				this.setFieldsDisabled(false);
				break;
			case 'view':
				this.setTitle(this.winTitle + langs(': Просмотр'));
				this.setFieldsDisabled(true);
				break;
		}

		var loadMask = new Ext.LoadMask(this.form.getEl(), {msg:langs('Загрузка...')});
		loadMask.show();

		switch ( win.action ) {
			case 'add':
				win.form.setValues(arguments[0].formParams);
				win.InformationPanel.load({
					Person_id: arguments[0].formParams.Person_id
				});
				loadMask.hide();
				win.form.findField('UslugaCategory_id').setFieldValue('UslugaCategory_SysNick', 'gost2011');
				win.setAllowedDates();
				win.form.findField('Lpu_Name').setValue(getGlobalOptions().lpu_nick);
				win.setUslugaComplexFilter();
				break;

			case 'edit':
			case 'view':
				Ext.Ajax.request({
					failure:function () {
						sw.swMsg.alert(langs('Ошибка'), langs('Не удалось получить данные с сервера'));
						loadMask.hide();
						win.hide();
					},
					params:{
						EvnUslugaOnkoNonSpec_id: win.EvnUslugaOnkoNonSpec_id
					},
					success: function (response) {
						var result = Ext.util.JSON.decode(response.responseText);
						if (result[0]) {
							win.form.setValues(result[0]);
							win.InformationPanel.load({
								Person_id: result[0].Person_id
							});
							loadMask.hide();

							var UslugaComplex_id = win.form.findField('UslugaComplex_id').getValue();
							win.setUslugaComplexFilter();

							if ( !Ext.isEmpty(UslugaComplex_id) ) {
								win.form.findField('UslugaComplex_id').getStore().load({
									callback: function() {
										if ( win.form.findField('UslugaComplex_id').getStore().getCount() > 0 ) {
											win.form.findField('UslugaComplex_id').setValue(UslugaComplex_id);
										}
										else {
											win.form.findField('UslugaComplex_id').clearValue();
										}
									}.createDelegate(this),
									params: {
										UslugaComplex_id: UslugaComplex_id
									}
								});
							}

							win.setAllowedDates();
						}
					},
					url:'/?c=EvnUslugaOnkoNonSpec&m=load'
				});
				break;
		}
		return true;
	},
	submit: function() {
		var win = this;

		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение..."});
		loadMask.show();

		var formParams = this.form.getValues();

		Ext.Ajax.request({
			failure:function () {
				loadMask.hide();
			},
			params: formParams,
			method: 'POST',
			success: function (result) {
				loadMask.hide();

				var response = Ext.util.JSON.decode(result.responseText);

				if ( !response.success ) {
					//сообщение уже выведено
				}
				else {
					if ( response.EvnUslugaCommon_id ) {
						if ( response.EvnUslugaCommon_id == -1 ) {
							showSysMsg('Добавленная услуга уже имеется в текущем случае лечения и не будет внесена повторно');
						}
						else {
							showSysMsg('Вы добавили новую услугу. Услуга скопирована в раздел "Услуги" текущего посещения / движения');
						}
					}

					formParams.EvnUslugaOnkoNonSpec_id = response.EvnUslugaOnkoNonSpec_id;
					win.callback(formParams);
					win.hide();
				}
			},
			url:'/?c=EvnUslugaOnkoNonSpec&m=save'
		});
	},

	/* конструктор */
	initComponent: function() {
		var win = this;

		this.InformationPanel = new sw.Promed.PersonInformationPanelShort({
			region: 'north'
		});

		var form = new Ext.Panel({
			autoHeight: true,
			autoScroll: true,
			bodyBorder: false,
			border: false,
			frame: false,
			region: 'center',
			items: [{
				title: langs('1. Лечение'),
				xtype: 'form',
				autoHeight: true,
				id: 'EvnUslugaOnkoNonSpecEditForm',
				bodyStyle:'background:#DFE8F6;padding:5px;',
				border: false,
				labelWidth: 200,
				collapsible: false,
				labelAlign: 'right',
				region: 'center',
				url:'/?c=EvnUslugaOnkoNonSpec&m=save',
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
					width: 450,
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
					xtype: 'swdatefield',
					plugins: [new Ext.ux.InputTextMask('99.99.9999', false)]
				}, {
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
							win.setUslugaComplexFilter();
						}
					},
					loadParams: {
						params: {
							where: "where UslugaCategory_SysNick in ('gost2011', 'lpu', 'tfoms')"
						}
					},
					width: 450,
					xtype: 'swuslugacategorycombo'
				}, {
					fieldLabel: langs('Услуга'),
					hiddenName: 'UslugaComplex_id',
					listeners: {
						'change': function(combo, newValue, oldValue) {
							var idx = combo.getStore().findBy(function(rec) {
								return rec.get(combo.valueField) == newValue;
							});
							combo.fireEvent('select', combo, combo.getStore().getAt(idx), idx);
						},
						'select': function(combo, record) {
							if ( typeof record == 'object' && !Ext.isEmpty(record.get('EvnUsluga_setDate')) ) {
								win.form.findField('EvnUslugaOnkoNonSpec_setDT').setValue(record.get('EvnUsluga_setDate').format('d.m.Y'));
							}
						}
					},
					listWidth: 700,
					to: 'EvnUslugaOnkoNonSpec',
					useEvnUslugaData: true,
					width: 450,
					xtype: 'swuslugacomplexnewcombo'
				}]
			}],
			reader: new Ext.data.JsonReader({
				success: Ext.emptyFn
			}, [
				{name: 'EvnUslugaOnkoNonSpec_id'},
				{name: 'EvnUslugaOnkoNonSpec_pid'},
				{name: 'Server_id'},
				{name: 'Person_id'},
				{name: 'PersonEvn_id'},
				{name: 'Lpu_Name'},
				{name: 'MorbusOnko_id'},
				{name: 'Morbus_id'},
				{name: 'EvnUslugaOnkoNonSpec_setDT'},
				{name: 'UslugaCategory_id'},
				{name: 'UslugaComplex_id'}
			]),
			url: '/?c=EvnUslugaOnkoNonSpec&m=save'
		});

		Ext.apply(this, {
			buttons: [{
				handler: function() {
					win.doSave();
				},
				iconCls: 'save16',
				text: BTN_FRMSAVE
			}, {
				text: '-'
			},
			HelpButton(this, 0),
			{
				handler: function() {
					win.hide();
				},
				iconCls: 'cancel16',
				text: BTN_FRMCANCEL
			}],
			items:[
				win.InformationPanel,
				form
			]
		});

		sw.Promed.swEvnUslugaOnkoNonSpecEditWindow.superclass.initComponent.apply(this, arguments);

		this.form = this.findById('EvnUslugaOnkoNonSpecEditForm').getForm();
	}
});