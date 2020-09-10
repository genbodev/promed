/**
 * swEvnUslugaOnkoChemEditWindow - окно редактирования "Химиотерапевтическое лечение"
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

Ext6.define('common.MorbusOnko.swEvnUslugaOnkoChemEditWindow', {
	/* свойства */
	requires: [
		'common.MorbusOnko.AddOnkoComplPanel',
		'common.EMK.PersonInfoPanelShort',
	],
	alias: 'widget.swEvnUslugaOnkoChemEditWindow',
    autoShow: false,
	closable: true,
	cls: 'arm-window-new emkd',
	constrain: true,
	extend: 'base.BaseForm',
	findWindow: false,
    header: true,
	modal: true,
	layout: 'form',
	refId: 'EvnUslugaOnkoChemeditsw',
	renderTo: Ext.getCmp('main-center-panel').body.dom,
	resizable: false,
	title: 'Химиотерапевтическое лечение',
	width: 820,
	maxHeight: main_center_panel.body.getHeight() - 50,
	
    deleteMorbusOnkoDrug: function() {
		var win = this;
		var base_form = this.FormPanel.getForm();
		var error = langs('При удалении возникли ошибки');
		var question = langs('Удалить препарат?');
		var grid = win.MorbusOnkoDrugFrame;
		
		var record = grid.getStore().getAt(grid.recordMenu.rowIndex);
		if (!record) return false;
		
		var object_id = record.get('MorbusOnkoDrug_id');
		if (!object_id) return false;
		
		var params = {};
		params['obj_isEvn'] = 'false';
		params['MorbusOnkoDrug_id'] = object_id;
		
		var onSuccess = function() {
            grid.getStore().load({
                params: {Evn_id: base_form.findField('EvnUslugaOnkoChem_id').getValue()}
            });
		};
		
		sw.swMsg.show({
			buttons: sw.swMsg.YESNO,
			fn: function(buttonId, text, obj) {
				if ( buttonId == 'yes' ) {
					win.mask(LOAD_WAIT_DELETE);
					Ext6.Ajax.request({
						failure: function(response, options) {
							win.unmask();
						},
						params: params,
						success: function(response, options) {
							win.unmask();
							var response_obj = Ext6.util.JSON.decode(response.responseText);
							if ( response_obj.success == false ) {
								sw.swMsg.alert(langs('Ошибка'), response_obj.Error_Msg ? response_obj.Error_Msg : error);
							} else {
								onSuccess({});
							}
						}.createDelegate(this),
						url: '/?c=MorbusOnkoDrug&m=destroy'
					});
				}
			}.createDelegate(this),
			icon: Ext6.MessageBox.QUESTION,
			msg: question,
			title: langs('Вопрос')
		});
        return true;
	},
	
	setAllowedDates: function() {
		var win = this;
		var base_form = this.FormPanel.getForm();
		var set_dt_field = base_form.findField('EvnUslugaOnkoChem_setDate');
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
		var set_dt_field = base_form.findField('EvnUslugaOnkoChem_setDate');
		var set_dt_value = null;
		if (!Ext.isEmpty(set_dt_field.getValue())) {
			set_dt_value = set_dt_field.getValue().format('d.m.Y');
		}
		var dis_dt_field = base_form.findField('EvnUslugaOnkoChem_disDate');

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
	
	onCancelAction: function() {
		var win = this;
		var EvnUslugaOnkoChem_id = this.FormPanel.getForm().findField('EvnUslugaOnkoChem_id').getValue();

		if ( !Ext.isEmpty(EvnUslugaOnkoChem_id) && this.actionAdd == true ) {
			win.mask("Удаление лечения...");
			Ext6.Ajax.request({
				callback: function(options, success, response) {
					win.unmask();
					if ( success ) {
						this.hide();
					}
					else {
						sw.swMsg.alert(langs('Ошибка'), langs('При удалении лечения возникли ошибки'));
						return false;
					}
				}.createDelegate(this),
				params: {
					Evn_id: EvnUslugaOnkoChem_id
				},
				url: '/?c=Evn&m=deleteEvn'
			});
		}
		else {
			this.hide();
		}
	},

	openMorbusOnkoDrugWindow: function(action) {
		if (!action || !action.toString().inlist(['add', 'edit', 'view'])) {
			return false;
		}

		if (getWnd('swMorbusOnkoDrugWindowExt6').isVisible()) {
			getWnd('swMorbusOnkoDrugWindowExt6').hide();
		}

		var thas = this;
		var grid = this.MorbusOnkoDrugFrame;
		var base_form = this.FormPanel.getForm();
		var selected_record = grid.getStore().getAt(grid.recordMenu.rowIndex);
		var params = {
			EvnUsluga_setDT: base_form.findField('EvnUslugaOnkoChem_setDate').getValue(),
			EvnUsluga_disDT: base_form.findField('EvnUslugaOnkoChem_disDate').getValue()
		};
		params.action = action;
		params.callback = function(data) {
			grid.getStore().load({
				params: {Evn_id: data.Evn_id}
			});
		};
		if (action == 'add') {
			this.onSave = null;
			var evn_id = base_form.findField('EvnUslugaOnkoChem_id').getValue();
			if (evn_id) {
				params.formParams = {
					MorbusOnko_id: base_form.findField('MorbusOnko_id').getValue(),
					Evn_id: evn_id,
					MorbusOnkoDrug_begDT: base_form.findField('EvnUslugaOnkoChem_setDate').getValue(),
					MorbusOnkoVizitPLDop_id: this.MorbusOnkoVizitPLDop_id,
					MorbusOnkoLeave_id: this.MorbusOnkoLeave_id,
					MorbusOnkoDiagPLStom_id: this.MorbusOnkoDiagPLStom_id
				};
				getWnd('swMorbusOnkoDrugWindowExt6').show(params);
			} else {
				this.onSave = function(evn_id){
					params.formParams = {
						MorbusOnko_id: base_form.findField('MorbusOnko_id').getValue(),
						Evn_id: evn_id,
						MorbusOnkoDrug_begDT: base_form.findField('EvnUslugaOnkoChem_setDate').getValue(),
						MorbusOnkoVizitPLDop_id: this.MorbusOnkoVizitPLDop_id,
						MorbusOnkoLeave_id: this.MorbusOnkoLeave_id,
						MorbusOnkoDiagPLStom_id: this.MorbusOnkoDiagPLStom_id
					};
					getWnd('swMorbusOnkoDrugWindowExt6').show(params);
					thas.onSave = null;
				};
				this.save({no_mod_check: true});
			}
		} else {
			if (!selected_record) {
				return false;
			}
			params.formParams = selected_record.data;
			params.onHide = function() {
				grid.getView().focusRow(grid.getStore().indexOf(selected_record));
			};
			getWnd('swMorbusOnkoDrugWindowExt6').show(params);
		}
		return true;
	},
	
	openMorbusOnkoDrugSelectWindow: function() {
		var wnd = this;
		var grid = this.MorbusOnkoDrugFrame;
		var base_form = this.FormPanel.getForm();

		var params = {
			MorbusOnko_id: base_form.findField('MorbusOnko_id').getValue(),
			Evn_id: base_form.findField('EvnUslugaOnkoChem_id').getValue()
		};

		params.callback = function() {
			grid.getStore().load({
				params: {Evn_id: params.Evn_id}
			});
		};

		if (params.Evn_id) {
			getWnd('swMorbusOnkoDrugSelectWindowExt6').show(params);
		} else {
			this.onSave = function(evn_id){
				params.Evn_id = evn_id;
				getWnd('swMorbusOnkoDrugSelectWindowExt6').show(params);
				wnd.onSave = null;
			};
			this.save({no_mod_check: true});
		}
	},
	
	/* методы */
	save: function (options) {
		var
			base_form = this.FormPanel.getForm(),
			win = this;

		if ( !base_form.isValid() ) {
			sw.swMsg.alert('Ошибка', 'Не все поля формы заполнены корректно');
			return false;
		}
		
        if (!options || Ext.isEmpty(options.no_mod_check)) {
            var mod_exists = false;
            this.MorbusOnkoDrugFrame.getStore().each(function(record) {
                if(record.get('MorbusOnkoDrug_id') > 0) {
                    mod_exists = true;
                    return false;
                }
            });
            if (!mod_exists) { //нет записсей в разделе "Препарат"
                sw.swMsg.show({
                    buttons: Ext.Msg.OK,
                    fn: function() {},
                    icon: Ext.Msg.WARNING,
                    msg: langs('Заполните раздел «Препарат»'),
                    title: ERR_INVFIELDS_TIT
                });
                return false;
            }
		}

		win.mask(LOAD_WAIT_SAVE);
		
        var params = {};
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
				
				if (typeof win.onSave == 'function') {
                    win.onSave(action.result.EvnUslugaOnkoChem_id);
                    base_form.findField('EvnUslugaOnkoChem_id').setValue(action.result.EvnUslugaOnkoChem_id);
                } else {
					win.callback();
					win.hide();
                }
			},
			failure: function(form, action) {
				win.unmask();
			}
		});
	},
	setUslugaComplexFilter: function() {
		var
			base_form = this.FormPanel.getForm(),
			UslugaCategory_SysNick = base_form.findField('UslugaCategory_id').getFieldValue('UslugaCategory_SysNick'),
			UslugaComplex_Date = base_form.findField('EvnUslugaOnkoChem_setDate').getValue();

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
		base_form.findField('UslugaComplex_id').lastQuery = 'This query sample that is not will never appear';

		base_form.findField('UslugaComplex_id').getStore().proxy.extraParams.UslugaComplex_Date = Ext.util.Format.date(UslugaComplex_Date, 'd.m.Y');
		base_form.findField('UslugaComplex_id').setUslugaCategoryList([ UslugaCategory_SysNick ]);
	},
	onSprLoad: function(arguments) {

		var win = this;
		var base_form = win.FormPanel.getForm();
		
		this.action = '';
		this.actionAdd = false;
		this.callback = Ext.emptyFn;
		this.EvnUslugaOnkoChem_id = null;

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
		if ( arguments[0].EvnUslugaOnkoChem_id ) {
			this.EvnUslugaOnkoChem_id = arguments[0].EvnUslugaOnkoChem_id;
		}
		if (!Ext.isEmpty(arguments[0].formParams.EvnPL_id))
			this.EvnPL_id = arguments[0].formParams.EvnPL_id;

        this.MorbusOnkoVizitPLDop_id = arguments[0].MorbusOnkoVizitPLDop_id || null;
        this.MorbusOnkoLeave_id = arguments[0].MorbusOnkoLeave_id || null;
        this.MorbusOnkoDiagPLStom_id = arguments[0].MorbusOnkoDiagPLStom_id || null;

		base_form.reset();

		var grid = this.MorbusOnkoDrugFrame;
		grid.getStore().removeAll();

		base_form.findField('DrugTherapyLineType_id').setContainerVisible(getRegionNick() != 'kz');
		base_form.findField('DrugTherapyLoopType_id').setContainerVisible(getRegionNick() != 'kz');
		base_form.findField('UslugaCategory_id').setContainerVisible(getRegionNick() != 'kz');
		base_form.findField('UslugaComplex_id').setContainerVisible(getRegionNick() != 'kz');

		base_form.findField('UslugaComplex_id').setAllowedUslugaComplexAttributeList([ 'XimLech' ]);
		
		base_form.findField('UslugaCategory_id').getStore().filterBy(function(rec) {
			return rec.get('UslugaCategory_SysNick').inlist(['gost2011','lpu','tfoms']);
		});

		switch (arguments[0].action) {
			case 'add':
				this.setTitle(langs('Химиотерапевтическое лечение: Добавление'));
				//this.setFieldsDisabled(false);
				this.actionAdd = true;
				break;
			case 'edit':
				this.setTitle(langs('Химиотерапевтическое лечение: Редактирование'));
				//this.setFieldsDisabled(false);
				break;
			case 'view':
				this.setTitle(langs('Химиотерапевтическое лечение: Просмотр'));
				//this.setFieldsDisabled(true);
				break;
		}
		
		win.mask(LOAD_WAIT);
		
		win.PersonInfoPanel.load({
			Person_id: arguments[0].formParams.Person_id
		});

		switch ( arguments[0].action ) {
			case 'add':
				base_form.setValues(arguments[0].formParams);
				win.unmask();
				win.setAllowedDates();
				this.AggTypePanel.setValues([null]);
				if ( getRegionNick() != 'kz' ) {
					base_form.findField('UslugaCategory_id').setFieldValue('UslugaCategory_SysNick', 'gost2011');
					win.setUslugaComplexFilter();
				}
				base_form.isValid();
				break;

			case 'edit':
			case 'view':
				grid.getStore().load({
					params: {Evn_id: win.EvnUslugaOnkoChem_id}
				});
				Ext.Ajax.request({
					failure:function () {
						sw.swMsg.alert(langs('Ошибка'), langs('Не удалось получить данные с сервера'));
						win.unmask();
						win.hide();
					},
					params:{
						EvnUslugaOnkoChem_id: win.EvnUslugaOnkoChem_id
					},
					success: function (response) {
						var result = Ext.util.JSON.decode(response.responseText);
						if (result[0]) {
							base_form.setValues(result[0]);
							if(result[0].AggTypes){
								win.AggTypePanel.setValues(result[0].AggTypes);
							} else {
								win.AggTypePanel.setValues([null]);
							}

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
					url:'/?c=EvnUslugaOnkoChem&m=load'
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
			collapsible: true,
			cls: 'emk_forms accordion-panel-window',
			bodyPadding: '15 25 15 37',
			title: 'ЛЕЧЕНИЕ',
			header: {
				cls: 'arrow-expander-panel',
				titlePosition: 2
			},
			defaults: {
				labelAlign: 'left',
				labelWidth: 200
			},
			reader: Ext6.create('Ext6.data.reader.Json', {
				type: 'json',
				model: win.id + '_FormModel'
			}),
			url: '/?c=EvnUslugaOnkoChem&m=save',
			items: [{
				name: 'EvnUslugaOnkoChem_id',
				xtype: 'hidden'
			}, {
				name: 'EvnUslugaOnkoChem_pid',
				xtype: 'hidden'
			}, {
				name: 'MorbusOnko_id',
				xtype: 'hidden'
			}, {
				name: 'Morbus_id',
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
					name: 'EvnUslugaOnkoChem_setDate',
					allowBlank: false,
					xtype: 'datefield',
					listeners: {
						'change': function(field){
							win.setAllowedDatesForDisField();
							win.setUslugaComplexFilter();
						}
					},
				}, {
					labelAlign: 'right',
					labelWidth: 80,
					allowBlank: false,
					fieldLabel: langs('Время'),
					name: 'EvnUslugaOnkoChem_setTime',
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
					name: 'EvnUslugaOnkoChem_disDate',
					xtype: 'datefield'
				}, {
					labelAlign: 'right',
					labelWidth: 80,
					fieldLabel: langs('Время'),
					name: 'EvnUslugaOnkoChem_disTime',
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
				to: 'EvnUslugaOnkoChem',
				width: 700,
				xtype: 'swUslugaComplexCombo'
			}, {
				fieldLabel: langs('Вид химиотерапии'),
				name: 'OnkoUslugaChemKindType_id',
				xtype: 'commonSprCombo',
				allowBlank: false,
				sortField:'OnkoUslugaChemKindType_Code',
				comboSubject: 'OnkoUslugaChemKindType',
				width: 700
			}, {
				fieldLabel: langs('Преимущественная направленность'),
				name: 'OnkoUslugaChemFocusType_id',
				xtype: 'commonSprCombo',
				allowBlank: false,
				sortField:'OnkoUslugaChemFocusType_Code',
				comboSubject: 'OnkoUslugaChemFocusType',
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
				fieldLabel: langs('Условие проведения лечения'),
				name: 'TreatmentConditionsType_id',
				comboSubject: 'TreatmentConditionsType',
				xtype: 'commonSprCombo',
				width: 700
			},
			win.AggTypePanel,
			{
				allowBlank: getRegionNick() == 'kz',
				fieldLabel: langs('Линия лекарственной терапии'),
				name: 'DrugTherapyLineType_id',
				comboSubject: 'DrugTherapyLineType',
				xtype: 'commonSprCombo',
				width: 700
			}, {
				allowBlank: getRegionNick() == 'kz',
				fieldLabel: langs('Цикл лекарственной терапии'),
				name: 'DrugTherapyLoopType_id',
				comboSubject: 'DrugTherapyLoopType',
				xtype: 'commonSprCombo',
				width: 700
			}]
		});

		win.MorbusOnkoDrugFrame = Ext6.create('Ext6.grid.Panel', {
			height: 100,
			recordMenu: Ext6.create('Ext6.menu.Menu', {
				width: 180,
				cls: 'disp-menu',
				items: [{
					text: 'Редактировать',
					iconCls:'menu_dispedit',
					handler: function() {
						win.openMorbusOnkoDrugWindow('edit');
					}
				}, {
					text: 'Удалить',
					iconCls:'menu_dispdel',
					handler: function() {
						win.deleteMorbusOnkoDrug();
					}
				}]
			}),
			showRecordMenu: function(el, rowIndx) {
				this.recordMenu.rowIndex = rowIndx;
				this.recordMenu.showBy(el);
			},
			store: new Ext6.data.Store({
				fields: [
					'MorbusOnkoDrug_id',
					'MorbusOnko_id',
					'Evn_id',
					'MorbusOnkoDrug_begDT',
					'MorbusOnkoDrug_endDT',
					'MorbusOnkoDrug_DatePeriod',
					'DrugDictType_Name',
					'OnkoDrug_Name',
					'MorbusOnkoDrug_SumDose',
				],
				proxy: {
					type: 'ajax',
					actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
					reader: {
						type: 'json',
						rootProperty: ''
					},
					url: '/?c=MorbusOnkoDrug&m=readList'
				}
			}),
			columns: [
				{dataIndex: 'MorbusOnkoDrug_id', tdCls: 'nameTdCls', type: 'int', header: 'ID', hidden: true},
				{dataIndex: 'MorbusOnko_id', tdCls: 'nameTdCls', type: 'int', hidden: true},
				{dataIndex: 'Evn_id', tdCls: 'nameTdCls', type: 'int', hidden: true},
				{dataIndex: 'MorbusOnkoDrug_begDT', tdCls: 'nameTdCls', type: 'date', hidden: true},
				{dataIndex: 'MorbusOnkoDrug_endDT', tdCls: 'nameTdCls', type: 'date', hidden: true},
				{dataIndex: 'MorbusOnkoDrug_DatePeriod', header: langs('Продолжительность'), flex: 2, renderer: function (value, el, record) {
					if (!record.get('MorbusOnkoDrug_begDT')) {
						return '';
					}
					var period = record.get('MorbusOnkoDrug_begDT');
					if (record.get('MorbusOnkoDrug_endDT')) {
						period += ' - '+ record.get('MorbusOnkoDrug_endDT');
					}
					return period;
				}},
				{dataIndex: 'DrugDictType_Name', tdCls: 'nameTdCls', type: 'string', header: langs('Справочник'), flex: 1},
				{dataIndex: 'OnkoDrug_Name', tdCls: 'nameTdCls', type: 'string', header: langs('Препарат'), flex: 3},
				{dataIndex: 'MorbusOnkoDrug_SumDose', tdCls: 'nameTdCls', type: 'string', header: langs('Суммарная доза'), flex: 1}, 
				{
					width: 40,
					dataIndex: 'PersonDispHist_Action',
					renderer: function (value, metaData, record) {
						return "<div class='x6-tool-threedots' onclick='Ext6.getCmp(\"" + win.MorbusOnkoDrugFrame.id + "\").showRecordMenu(this, " + metaData.rowIndex + ");'></div>";
					}
				}
			],
			listeners: {
				
			}
		});
		
		win.MorbusOnkoDrugPanel = new Ext6.form.FormPanel({
			border: false,
			collapsible: true,
			cls: 'emk_forms accordion-panel-window',
			bodyPadding: '15 25 15 37',
			title: 'ПРЕПАРАТ',
			header: {
				cls: 'arrow-expander-panel',
				titlePosition: 1
			},
			tools: [{
				tooltip: 'Добавить',
				type: 'plusmenu',
				callback: function(panel, tool, event) {
					win.openMorbusOnkoDrugWindow('add');
				}
			}, {
				tooltip: 'Добавить препарат из специфики',
				type: 'plusmenu',
				callback: function(panel, tool, event) {
					win.openMorbusOnkoDrugSelectWindow();
				}
			}],
			items: [
				win.MorbusOnkoDrugFrame
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
						win.FormPanel,
						win.MorbusOnkoDrugPanel
					]
				}
			],
			buttons: [{
				xtype: 'SimpleButton',
				handler:function () {
					win.onCancelAction();
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