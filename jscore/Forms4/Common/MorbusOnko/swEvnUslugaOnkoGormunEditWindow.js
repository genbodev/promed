/**
 * swEvnUslugaOnkoGormunEditWindow - окно редактирования "Гормоноиммунотерапевтическое лечение"
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

Ext6.define('common.MorbusOnko.swEvnUslugaOnkoGormunEditWindow', {
	/* свойства */
	requires: [
		'common.MorbusOnko.AddOnkoComplPanel',
		'common.EMK.PersonInfoPanelShort',
	],
	alias: 'widget.swEvnUslugaOnkoGormunEditWindow',
    autoShow: false,
	closable: true,
	cls: 'arm-window-new emkd',
	constrain: true,
	extend: 'base.BaseForm',
	findWindow: false,
    header: true,
	modal: true,
	layout: 'form',
	refId: 'EvnUslugaOnkoGormuneditsw',
	renderTo: Ext.getCmp('main-center-panel').body.dom,
	resizable: false,
	title: 'Гормоноиммунотерапевтическое лечение',
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
                params: {Evn_id: base_form.findField('EvnUslugaOnkoGormun_id').getValue()}
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
    disabledDatePeriods: null,
    setAllowedDates: function() {
        var win = this;
		var base_form = this.FormPanel.getForm();
        var set_dt_field = base_form.findField('EvnUslugaOnkoGormun_setDate');
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
                       // set_dt_field.setAllowedDates(null);
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
        var set_dt_field = base_form.findField('EvnUslugaOnkoGormun_setDate');
        var set_dt_value = null;
        if (!Ext.isEmpty(set_dt_field.getValue())) {
            set_dt_value = set_dt_field.getValue().format('d.m.Y');
        }
        var dis_dt_field = base_form.findField('EvnUslugaOnkoGormun_disDate');

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
		var EvnUslugaOnkoGormun_id = this.FormPanel.getForm().findField('EvnUslugaOnkoGormun_id').getValue();

		if ( !Ext.isEmpty(EvnUslugaOnkoGormun_id) && this.actionAdd == true ) {
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
					Evn_id: EvnUslugaOnkoGormun_id
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
        var selected_record = grid.getSelectionModel().getSelected();
        var params = {
            EvnUsluga_setDT: base_form.findField('EvnUslugaOnkoGormun_setDate').getValue(),
            EvnUsluga_disDT: base_form.findField('EvnUslugaOnkoGormun_disDate').getValue()
        };
        params.action = action;
        params.callback = function(data) {
            grid.getStore().load({
                params: {Evn_id: data.Evn_id}
            });
        };
        if (action == 'add') {
            this.onSave = null;
            var evn_id = base_form.findField('EvnUslugaOnkoGormun_id').getValue();
            if (evn_id) {
                params.formParams = {
                    MorbusOnko_id: base_form.findField('MorbusOnko_id').getValue(),
                    Evn_id: evn_id,
                    MorbusOnkoDrug_begDT: base_form.findField('EvnUslugaOnkoGormun_setDate').getValue(),
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
                        MorbusOnkoDrug_begDT: base_form.findField('EvnUslugaOnkoGormun_setDate').getValue(),
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
            Evn_id: base_form.findField('EvnUslugaOnkoGormun_id').getValue()
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
			win = this,
			base_form = this.FormPanel.getForm(),
			formParams = base_form.getValues();

		if ( !base_form.isValid() ) {
			sw.swMsg.alert('Ошибка', 'Не все поля формы заполнены корректно');
			return false;
		}

        if (formParams.EvnUslugaOnkoGormun_IsBeam != 2 &&
            formParams.EvnUslugaOnkoGormun_IsSurg != 2 &&
            formParams.EvnUslugaOnkoGormun_IsDrug != 2 &&
            formParams.EvnUslugaOnkoGormun_IsOther != 2
        ) {
			sw.swMsg.alert('Ошибка', 'Обязательно выбрать хотя бы один вид гормоноиммунотерапии');
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
				
				if (typeof win.onSave == 'function') {
					win.onSave(action.result.EvnUslugaOnkoGormun_id);
					base_form.findField('EvnUslugaOnkoGormun_id').setValue(action.result.EvnUslugaOnkoGormun_id);
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
    setOnkoRadiotherapyFilter: function() {
        var
            base_form = this.FormPanel.getForm(),
            EvnUslugaOnkoRadiotherapy_setDate = base_form.findField('EvnUslugaOnkoBeam_setDate').getValue(),
            index = -1,
            OnkoRadiotherapy_id = base_form.findField('OnkoRadiotherapy_id').getValue();

        if ( Ext.isEmpty(EvnUslugaOnkoRadiotherapy_setDate) ) {
            EvnUslugaOnkoRadiotherapy_setDate = getValidDT(getGlobalOptions().date, '');
        }

        base_form.findField('OnkoRadiotherapy_id').getStore().clearFilter();
        base_form.findField('OnkoRadiotherapy_id').clearValue();
        base_form.findField('OnkoRadiotherapy_id').lastQuery = '';

        base_form.findField('OnkoRadiotherapy_id').getStore().filterBy(function(rec) {
            return (
                (!rec.get('OnkoRadiotherapy_begDate') || rec.get('OnkoRadiotherapy_begDate') <= EvnUslugaOnkoRadiotherapy_setDate)
                && (!rec.get('OnkoRadiotherapy_endDate') || rec.get('OnkoRadiotherapy_endDate') >= EvnUslugaOnkoRadiotherapy_setDate)
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
			EvnUslugaOnkoGormun_setDate = base_form.findField('EvnUslugaOnkoGormun_setDate').getValue(),
			index = -1,
			OnkoRadiotherapy_id = base_form.findField('OnkoRadiotherapy_id').getValue();

			if ( Ext.isEmpty(EvnUslugaOnkoGormun_setDate) ) {
				EvnUslugaOnkoGormun_setDate = getValidDT(getGlobalOptions().date, '');
			}
			
			base_form.findField('OnkoRadiotherapy_id').getStore().clearFilter();
			base_form.findField('OnkoRadiotherapy_id').clearValue();
			base_form.findField('OnkoRadiotherapy_id').lastQuery = '';

			base_form.findField('OnkoRadiotherapy_id').getStore().filterBy(function(rec) {
				return (
					(!rec.get('OnkoRadiotherapy_begDate') || (rec.get('OnkoRadiotherapy_begDate') <= EvnUslugaOnkoGormun_setDate))
					&& (!rec.get('OnkoRadiotherapy_endDate') || (rec.get('OnkoRadiotherapy_endDate') >= EvnUslugaOnkoGormun_setDate))
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
			base_form = this.FormPanel.getForm(),
			UslugaCategory_SysNick = base_form.findField('UslugaCategory_id').getFieldValue('UslugaCategory_SysNick'),
			UslugaComplex_Date = base_form.findField('EvnUslugaOnkoGormun_setDate').getValue();

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
		var gormuntype_cbgroup = this.FormPanel.getComponent(this.id + 'EvnUslugaOnkoGormunTypeCbGroup');

		win.action = (typeof arguments[0].action == 'string' ? arguments[0].action : 'add');
		win.callback = (typeof arguments[0].callback == 'function' ? arguments[0].callback : Ext6.emptyFn);
		win.formParams = (typeof arguments[0].formParams == 'object' ? arguments[0].formParams : {});
		
		this.actionAdd = false;

		win.center();
		win.setTitle('Гормоноиммунотерапевтическое лечение');
		
		if (arguments[0]['EvnUslugaOnkoGormun_id']) {
			this.EvnUslugaOnkoGormun_id = arguments[0]['EvnUslugaOnkoGormun_id'];
		} else {
			this.EvnUslugaOnkoGormun_id = null;
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

		var base_form = win.FormPanel.getForm();
		base_form.reset();
		base_form.setValues(win.formParams);

		var grid = this.MorbusOnkoDrugFrame;
		grid.getStore().removeAll();
		
		win.PersonInfoPanel.load({
			Person_id: arguments[0].formParams.Person_id
		});
		
		base_form.findField('UslugaCategory_id').getStore().filterBy(function(rec) {
			return rec.get('UslugaCategory_SysNick').inlist(['gost2011','lpu','tfoms']);
		});

		switch ( win.action ) {
			case 'add':
				this.actionAdd = true;
				win.setTitle(win.getTitle() + ': Добавление');
				base_form.findField('EvnUslugaOnkoGormun_setDate').focus();
                win.AggTypePanel.setValues([null]);
				if ( getRegionNick() != 'kz' ) {
					base_form.findField('UslugaCategory_id').setFieldValue('UslugaCategory_SysNick', 'gost2011');
					win.setUslugaComplexFilter();
				}
				gormuntype_cbgroup.fireEvent('change', gormuntype_cbgroup, gormuntype_cbgroup.getValue());
				//win.setOnkoRadiotherapyFilter();
				win.setOnkoGormunFilter();
				win.setAllowedDates();
				base_form.isValid();
				break;

			case 'edit':
				win.setTitle(win.getTitle() + ': Редактирование');

				win.mask(LOAD_WAIT);

				grid.getStore().load({
					params: {Evn_id: win.EvnUslugaOnkoGormun_id}
				});

				base_form.load({
					url: '/?c=EvnUslugaOnkoGormun&m=load',
					params: {
						EvnUslugaOnkoGormun_id: base_form.findField('EvnUslugaOnkoGormun_id').getValue()
					},
					success: function(form, action) {
						win.unmask();
						base_form.findField('EvnUslugaOnkoGormun_setDate').focus();
						var result = Ext.util.JSON.decode(action.response.responseText);
						if (result[0]) {
                            if(result[0].AggTypes){
								win.AggTypePanel.setValues(result[0].AggTypes);
							} else {
								win.AggTypePanel.setValues([null]);
							}
							
							var UslugaComplex_id = result[0].UslugaComplex_id || null;
							
							//win.setOnkoRadiotherapyFilter();
							win.setOnkoGormunFilter();
							win.setUslugaComplexFilter();
							
							gormuntype_cbgroup.fireEvent('change', gormuntype_cbgroup, gormuntype_cbgroup.getValue());

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
						base_form.isValid();
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
			url: '/?c=EvnUslugaOnkoGormun&m=save',
			items: [{
				name: 'EvnUslugaOnkoGormun_id',
				xtype: 'hidden'
			}, {
				name: 'EvnUslugaOnkoGormun_pid',
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
					name: 'EvnUslugaOnkoGormun_setDate',
					allowBlank: false,
					xtype: 'datefield',
					listeners: {
						'change': function(field){
							win.setAllowedDatesForDisField();
							win.setUslugaComplexFilter();
							//win.setOnkoRadiotherapyFilter();
							win.setOnkoGormunFilter();
						}
					},
				}, {
					labelAlign: 'right',
					labelWidth: 80,
					allowBlank: false,
					fieldLabel: langs('Время'),
					name: 'EvnUslugaOnkoGormun_setTime',
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
					name: 'EvnUslugaOnkoGormun_disDate',
					xtype: 'datefield'
				}, {
					labelAlign: 'right',
					labelWidth: 80,
					fieldLabel: langs('Время'),
					name: 'EvnUslugaOnkoGormun_disTime',
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
				to: 'EvnUslugaOnkoGormun',
				width: 700,
				xtype: 'swUslugaComplexCombo'
			}, {
				xtype: 'checkboxgroup',
				id: this.id + 'EvnUslugaOnkoGormunTypeCbGroup',
				width: 700,
				fieldLabel: 'Вид гормоноиммунотерапии',
				columns: [0.3, 0.2, 0.3, 0.3],
				vertical: true,
				items: [
					{ boxLabel: langs('лекарственная'), name: 'EvnUslugaOnkoGormun_IsDrug', uncheckedValue: '1', inputValue: '2' },
					{ boxLabel: langs('лучевая'), name: 'EvnUslugaOnkoGormun_IsBeam', uncheckedValue: '1', inputValue: '2' },
					{ boxLabel: langs('хирургическая'), name: 'EvnUslugaOnkoGormun_IsSurg', uncheckedValue: '1', inputValue: '2' },
					{ boxLabel: langs('(неизвестно)'), name: 'EvnUslugaOnkoGormun_IsOther', uncheckedValue: '1', inputValue: '2' }
				], 
				listeners: {
					change: function(checkbox, chk){
						var base_form = win.FormPanel.getForm();
						var checked;
						
						// лекарственная 
						checked = !!chk.EvnUslugaOnkoGormun_IsDrug;
						if (checked) {
							base_form.findField('EvnUslugaOnkoGormun_IsOther').setValue(false);
						}

						if ( getRegionNick() != 'kz' ) {
							base_form.findField('DrugTherapyLineType_id').setAllowBlank(!checked || base_form.findField('EvnUslugaOnkoGormun_IsBeam').getValue() == true || base_form.findField('EvnUslugaOnkoGormun_IsSurg').getValue() == true);
							base_form.findField('DrugTherapyLineType_id').setContainerVisible(checked);
							base_form.findField('DrugTherapyLoopType_id').setAllowBlank(!checked || base_form.findField('EvnUslugaOnkoGormun_IsBeam').getValue() == true || base_form.findField('EvnUslugaOnkoGormun_IsSurg').getValue() == true);
							base_form.findField('DrugTherapyLoopType_id').setContainerVisible(checked);

							if ( !checked ) {
								base_form.findField('DrugTherapyLineType_id').clearValue();
								base_form.findField('DrugTherapyLoopType_id').clearValue();
							}
						}
						
						// лучевая
						checked = !!chk.EvnUslugaOnkoGormun_IsBeam;
						if (checked) {
							base_form.findField('EvnUslugaOnkoGormun_IsOther').setValue(false);
						}

						base_form.findField('OnkoRadiotherapy_id').setAllowBlank(!getRegionNick().inlist(['astra', 'adygeya']) || !checked);
						base_form.findField('OnkoRadiotherapy_id').setContainerVisible(checked);
						base_form.findField('EvnUslugaOnkoGormun_CountFractionRT').setAllowBlank(!checked);
						base_form.findField('EvnUslugaOnkoGormun_CountFractionRT').setContainerVisible(checked);
						base_form.findField('EvnUslugaOnkoGormun_TotalDoseTumor').setAllowBlank((getRegionNick() != 'adygeya' && (getRegionNick() != 'astra' || base_form.findField('EvnUslugaOnkoGormun_IsDrug').getValue() == true)) || !checked);
						base_form.findField('EvnUslugaOnkoGormun_TotalDoseTumor').setContainerVisible(checked);
						base_form.findField('EvnUslugaOnkoGormun_TotalDoseRegZone').setAllowBlank(getRegionNick() != 'astra' || !checked || base_form.findField('EvnUslugaOnkoGormun_IsDrug').getValue() == true);
						base_form.findField('EvnUslugaOnkoGormun_TotalDoseRegZone').setContainerVisible(checked);

						base_form.findField('DrugTherapyLineType_id').setAllowBlank(base_form.findField('EvnUslugaOnkoGormun_IsDrug').getValue() == false || checked || base_form.findField('EvnUslugaOnkoGormun_IsSurg').getValue() == true);
						base_form.findField('DrugTherapyLoopType_id').setAllowBlank(base_form.findField('EvnUslugaOnkoGormun_IsDrug').getValue() == false || checked || base_form.findField('EvnUslugaOnkoGormun_IsSurg').getValue() == true);

						if ( !checked ) {
							base_form.findField('OnkoRadiotherapy_id').clearValue();
							base_form.findField('EvnUslugaOnkoGormun_CountFractionRT').setValue(null);
							base_form.findField('EvnUslugaOnkoGormun_TotalDoseTumor').setValue(null);
							base_form.findField('EvnUslugaOnkoGormun_TotalDoseRegZone').setValue(null);
						}
						
						// хирургическая
						checked = !!chk.EvnUslugaOnkoGormun_IsSurg;
						if (checked) {
							base_form.findField('EvnUslugaOnkoGormun_IsOther').setValue(false);
						}

						base_form.findField('DrugTherapyLineType_id').setAllowBlank(base_form.findField('EvnUslugaOnkoGormun_IsDrug').getValue() == false || base_form.findField('EvnUslugaOnkoGormun_IsBeam').getValue() == true || checked);
						base_form.findField('DrugTherapyLoopType_id').setAllowBlank(base_form.findField('EvnUslugaOnkoGormun_IsDrug').getValue() == false || base_form.findField('EvnUslugaOnkoGormun_IsBeam').getValue() == true || checked);
						
						// неизвестно
						checked = !!chk.EvnUslugaOnkoGormun_IsOther;
						if (checked) {
							base_form.findField('EvnUslugaOnkoGormun_IsBeam').setValue(false);
							base_form.findField('EvnUslugaOnkoGormun_IsSurg').setValue(false);
							base_form.findField('EvnUslugaOnkoGormun_IsDrug').setValue(false);
						}
					}
				}
			}, {
				fieldLabel: langs('Преимущественная направленность'),
				name: 'OnkoUslugaGormunFocusType_id',
				xtype: 'commonSprCombo',
				allowBlank: false,
				sortField:'OnkoUslugaGormunFocusType_Code',
				comboSubject: 'OnkoUslugaGormunFocusType',
				width: 700
			}, {
				comboSubject: 'OnkoRadiotherapy',
				name: 'OnkoRadiotherapy_id',
				fieldLabel: langs('Тип лечения'),
				moreFields: [
					{name: 'OnkoRadiotherapy_begDate', mapping: 'OnkoRadiotherapy_begDate', type: 'date',  dateFormat: 'd.m.Y'},
					{name: 'OnkoRadiotherapy_endDate', mapping: 'OnkoRadiotherapy_endDate', type: 'date',  dateFormat: 'd.m.Y'}
				],
				xtype: 'commonSprCombo',
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
				comboSubject: 'TreatmentConditionsType',
				xtype: 'commonSprCombo',
				width: 700
			},
			win.AggTypePanel,
			{
				fieldLabel: langs('Линия лекарственной терапии'),
				comboSubject: 'DrugTherapyLineType',
				name: 'DrugTherapyLineType_id',
				xtype: 'commonSprCombo',
				width: 700
			}, {
				fieldLabel: langs('Цикл лекарственной терапии'),
				comboSubject: 'DrugTherapyLoopType',
				name: 'DrugTherapyLoopType_id',
				xtype: 'commonSprCombo',
				width: 700
			}, {
				allowBlank: false,
				allowDecimals: false,
				minValue: 0,
				fieldLabel: langs('Кол-во фракций проведения лучевой терапии'),
				name: 'EvnUslugaOnkoGormun_CountFractionRT',
				labelWidth: 430,
				width: 545,
				xtype: 'numberfield'
			}, {
				allowBlank: false,
				allowDecimals: true,
				minValue: 0,
				decimalPrecision: 3,
				fieldLabel: langs('Суммарная доза облучения опухоли'),
				name: 'EvnUslugaOnkoGormun_TotalDoseTumor',
				autoCreate: {tag: "input", maxLength: "8", autocomplete: "off"},
				labelWidth: 430,
				width: 545,
				xtype: 'numberfield'
			}, {
				allowBlank: false,
				allowDecimals: true,
				minValue: 0,
				decimalPrecision: 3,
				fieldLabel: langs('Суммарная доза облучения зон регионарного метастазирования'),
				name: 'EvnUslugaOnkoGormun_TotalDoseRegZone',
				autoCreate: {tag: "input", maxLength: "8", autocomplete: "off"},
				labelWidth: 430,
				width: 545,
				xtype: 'numberfield'
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