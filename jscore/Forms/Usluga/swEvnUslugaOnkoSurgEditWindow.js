/**
 * swEvnUslugaOnkoSurgEditWindow - окно редактирования "Хирургическое лечение"
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

sw.Promed.swEvnUslugaOnkoSurgEditWindow = Ext.extend(sw.Promed.BaseForm, {
    action: null,
	autoHeight: true,
	autoScroll: true,
    buttonAlign: 'left',
    callback: Ext.emptyFn,
    closable: true,
    id: 'swEvnUslugaOnkoSurgEditWindow',
    closeAction: 'hide',
    draggable: true,
    formMode: 'remote',
    formStatus: 'edit',
    layout: 'form',
    modal: true,
    width: 850,
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
                        thas.findById('EvnUslugaOnkoSurgEditForm').getFirstInvalidEl().focus(true);
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
        var AggTypes2 = this.AggTypePanel2.getValues();
        params.AggTypes2 = (AggTypes2.length > 1 ? AggTypes2.join(',') : AggTypes2);
        this.form.submit({
            params: params,
            failure: function(result_form, action)
            {
                loadMask.hide();
                if (action.result)
                {
                    if (action.result.Error_Code)
                    {
                        Ext.Msg.alert(lang['oshibka_#']+action.result.Error_Code, action.result.Error_Message);
                    }
                }
            },
            success: function(result_form, action)
            {
                loadMask.hide();
				if (action.result.EvnUslugaOper_id) {
					if (action.result.EvnUslugaOper_id == -1) {
						showSysMsg('Добавленная услуга уже имеется в текущем случае лечения и не будет внесена повторно');
					}
					else {
						showSysMsg('Вы добавили новую услугу. Услуга скопирована в раздел "Услуги" текущего посещения / движения');
					}
				}
                thas.callback(thas.owner, action.result.EvnUslugaOnkoSurg_id);
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
	setAllowedDates: function() {
		var that = this;
		var set_dt_field = that.form.findField('EvnUslugaOnkoSurg_setDate');
		var morbus_id = that.form.findField('Morbus_id').getValue();
        var morbusonkovizitpldop_id = that.MorbusOnkoVizitPLDop_id;
        var morbusonkoleave_id = that.MorbusOnkoLeave_id;
        var morbusonkodiagplstom_id = that.MorbusOnkoDiagPLStom_id;
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
					if (result[0] && Ext.isArray(result[0].disabledDates) && result[0].disabledDates.length > 0) {
						set_dt_field.setAllowedDates(result[0].disabledDates);
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
	setUslugaFieldsParams: function(loadOnEdit) {
		var
			base_form = this.form,
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

			if ( getRegionNick() == 'ufa' ) {
				list.push('gost2011');
			}
			else {
				list.push('Kod7');
			}

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

		this.syncSize();
		this.syncShadow();
	},
	setUslugaComplexFilter: function() {
		var
			base_form = this.form,
			Lpu_uid = base_form.findField('Lpu_uid').getValue(),
			UslugaCategory_SysNick = base_form.findField('UslugaCategory_id').getFieldValue('UslugaCategory_SysNick'),
			UslugaComplex_Date = base_form.findField('EvnUslugaOnkoSurg_setDate').getValue();

		if (
			(
				Ext.isEmpty(UslugaCategory_SysNick)
				|| base_form.findField('UslugaComplex_id').getStore().baseParams.uslugaCategoryList == Ext.util.JSON.encode([ UslugaCategory_SysNick ])
			)
			&& (
				base_form.findField('UslugaComplex_id').getStore().baseParams.Lpu_uid == Lpu_uid
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

		base_form.findField('UslugaComplex_id').getStore().baseParams.Lpu_uid = Lpu_uid;
		base_form.findField('UslugaComplex_id').getStore().baseParams.UslugaComplex_Date = Ext.util.Format.date(UslugaComplex_Date, 'd.m.Y');
		base_form.findField('UslugaComplex_id').setUslugaCategoryList([ UslugaCategory_SysNick ]);
	},
    show: function() {
        var win = this;
        var thas = this;
        var set_dt_field = thas.form.findField('EvnUslugaOnkoSurg_setDate');
        sw.Promed.swEvnUslugaOnkoSurgEditWindow.superclass.show.apply(this, arguments);
        this.action = '';
        this.callback = Ext.emptyFn;
		this.EvnPL_id = null;
		this.EvnSection_id = null;
        this.EvnUslugaOnkoSurg_id = null;
        this.EvnUslugaOnkoSurg_pid = null;
        this.ParentEvnClass_SysNick = 'EvnVizitPL';
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
        if ( arguments[0].EvnUslugaOnkoSurg_id ) {
            this.EvnUslugaOnkoSurg_id = arguments[0].EvnUslugaOnkoSurg_id;
        }
        if ( arguments[0].ParentEvnClass_SysNick ) {
            this.ParentEvnClass_SysNick = arguments[0].ParentEvnClass_SysNick;
        }
        if( arguments[0].EvnSection_id ) {
            this.EvnSection_id = arguments[0].EvnSection_id;
        }

        if (arguments[0].formParams.EvnUslugaOnkoSurg_pid) {
            this.EvnUslugaOnkoSurg_pid = arguments[0].formParams.EvnUslugaOnkoSurg_pid;
        }
		if (!Ext.isEmpty(arguments[0].formParams.EvnPL_id))
			this.EvnPL_id = arguments[0].formParams.EvnPL_id;

		this.MorbusOnkoVizitPLDop_id = arguments[0].MorbusOnkoVizitPLDop_id || null;
        this.MorbusOnkoLeave_id = arguments[0].MorbusOnkoLeave_id || null;
        this.MorbusOnkoDiagPLStom_id = arguments[0].MorbusOnkoDiagPLStom_id || null;
        this.form.reset();

		this.form.findField('UslugaCategory_id').listMode = null;

        var lpu_combo = thas.form.findField('Lpu_uid');
        var mp_combo = thas.form.findField('MedPersonal_id');
        var usluga_combo = this.form.findField('UslugaComplex_id');

        mp_combo.lastQuery = '';
        mp_combo.clearValue();
        mp_combo.getStore().removeAll();

        switch (arguments[0].action) {
            case 'add':
                this.setTitle(lang['hirurgicheskoe_lechenie_dobavlenie']);
                this.setFieldsDisabled(false);
                break;
            case 'edit':
                this.setTitle(lang['hirurgicheskoe_lechenie_redaktirovanie']);
                this.setFieldsDisabled(false);
                break;
            case 'view':
                this.setTitle(lang['hirurgicheskoe_lechenie_prosmotr']);
                this.setFieldsDisabled(true);
                break;
        }

        var loadMask = new Ext.LoadMask(this.form.getEl(), {msg:lang['zagruzka']});
        loadMask.show();

		win.ajaxCnt = 2;

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
        Ext.Ajax.request({
            url: '/?c=EvnPL&m=getLastVizitDT',
            params: params,
            success: function (response) {
				win.ajaxCnt = win.ajaxCnt - 1;

				if ( win.ajaxCnt == 0 ) {
					loadMask.hide();
				}

                var result = Ext.util.JSON.decode(response.responseText);
                var endTreatDate = getValidDT(!Ext.isEmpty(result.endTreatDate) ? result.endTreatDate : getGlobalOptions().date, '');

                win.form.findField('OnkoSurgicalType_id').getStore().clearFilter();
                win.form.findField('OnkoSurgicalType_id').lastQuery = '';	
                win.form.findField('OnkoSurgicalType_id').getStore().filterBy(function(rec) {
                    return (	
                        (Ext.isEmpty(rec.get('OnkoSurgicalType_begDate')) || rec.get('OnkoSurgicalType_begDate') <= endTreatDate)
                        && (Ext.isEmpty(rec.get('OnkoSurgicalType_endDate')) || rec.get('OnkoSurgicalType_endDate') >= endTreatDate)
                    );
                });
                win.form.findField('OnkoSurgicalType_id').baseFilterFn = function(rec) {
                    return (	
                        (Ext.isEmpty(rec.get('OnkoSurgicalType_begDate')) || rec.get('OnkoSurgicalType_begDate') <= endTreatDate)
                        && (Ext.isEmpty(rec.get('OnkoSurgicalType_endDate')) || rec.get('OnkoSurgicalType_endDate') >= endTreatDate)
                    );
                };
            }
        });

        switch (arguments[0].action) {
            case 'add':
                if (!arguments[0].formParams.EvnUslugaOnkoSurg_setDate) {
                    arguments[0].formParams.EvnUslugaOnkoSurg_setDate = getGlobalOptions().date;
                }
                thas.form.setValues(arguments[0].formParams);
                thas.InformationPanel.load({
                    Person_id: arguments[0].formParams.Person_id
                });
                if (set_dt_field.getValue()) {
                    set_dt_field.fireEvent('change', set_dt_field, set_dt_field.getValue(), null);
                }
                if (lpu_combo.getValue()) {
                    lpu_combo.fireEvent('change', lpu_combo, lpu_combo.getValue(), null);
                }

				thas.setUslugaFieldsParams();
				thas.setUslugaComplexFilter();

                if (arguments[0].formParams.EvnUslugaOnkoSurg_pid) {
                    Ext.Ajax.request({
                        failure:function () {
							win.ajaxCnt = win.ajaxCnt - 1;

							if ( win.ajaxCnt == 0 ) {
								loadMask.hide();
							}

							thas.setAllowedDates();
                        },
                        params:{
                            EvnUslugaOnkoSurg_pid: arguments[0].formParams.EvnUslugaOnkoSurg_pid
                        },
                        success: function (response) {
							win.ajaxCnt = win.ajaxCnt - 1;

							if ( win.ajaxCnt == 0 ) {
								loadMask.hide();
							}

							thas.setAllowedDates();
                            var result = Ext.util.JSON.decode(response.responseText);
                            if (result.success && result.TreatmentConditionsType_id) {
                                thas.form.findField('TreatmentConditionsType_id').setValue(result.TreatmentConditionsType_id);
                            }
                        },
                        url:'/?c=EvnUslugaOnkoSurg&m=getDefaultTreatmentConditionsTypeId'
                    });
                } else {
                    loadMask.hide();
					thas.setAllowedDates();
                }
                this.AggTypePanel.setValues([null]);
                this.AggTypePanel2.setValues([null]);
                break;

			case 'edit':
            case 'view':
                Ext.Ajax.request({
                    failure:function () {
                        sw.swMsg.alert(lang['oshibka'], lang['ne_udalos_poluchit_dannyie_s_servera']);

						win.ajaxCnt = win.ajaxCnt - 1;

						if ( win.ajaxCnt == 0 ) {
							loadMask.hide();
						}

                        thas.hide();
                    },
                    params:{
                        EvnUslugaOnkoSurg_id: thas.EvnUslugaOnkoSurg_id,
						archiveRecord: thas.archiveRecord
                    },
                    success: function (response) {
						win.ajaxCnt = win.ajaxCnt - 1;

						if ( win.ajaxCnt == 0 ) {
							loadMask.hide();
						}

                        var result = Ext.util.JSON.decode(response.responseText);
                        if (result[0]) {
                            thas.form.setValues(result[0]);
                            thas.InformationPanel.load({
                                Person_id: result[0].Person_id
                            });
							thas.setAllowedDates();
                            if(!result[0].AggTypes && !result[0].AggTypes2){
                                thas.AggTypePanel.setValues([null]);
                                thas.AggTypePanel2.setValues([null]);
                            } else {
                                thas.AggTypePanel.setValues(result[0].AggTypes);
                                thas.AggTypePanel2.setValues(result[0].AggTypes2);
                            }
                            var usluga_complex_id = usluga_combo.getValue();
                            set_dt_field.fireEvent('change', set_dt_field, set_dt_field.getValue(), null);
                            lpu_combo.fireEvent('change', lpu_combo, lpu_combo.getValue(), null);

							thas.setUslugaFieldsParams(true);
							thas.setUslugaComplexFilter();

                            if ( !Ext.isEmpty(usluga_complex_id) ) {
                                usluga_combo.getStore().load({
                                    callback: function() {
                                        if ( usluga_combo.getStore().getCount() > 0 ) {
                                            usluga_combo.setValue(usluga_complex_id);
                                        }
										else {
                                            usluga_combo.clearValue();
                                        }
                                    },
                                    params: {
                                        UslugaComplex_id: usluga_complex_id
                                    }
                                });
                            }
                        }
                    },
                    url:'/?c=EvnUslugaOnkoSurg&m=load'
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
            fieldLabelTitle: lang['intraoperatsionnoe_oslojnenie'],
            win: this,
            width: 810,
            buttonAlign: 'left',
            buttonLeftMargin: 150,
            labelWidth: 230,
            fieldWidth: 400,
            style: 'background: #DFE8F6'
        });
        this.AggTypePanel2 = new sw.Promed.AddOnkoComplPanel({
            objectName: 'AggType',
            fieldLabelTitle: lang['posleoperatsionnoe_oslojnenie'],
            win: this,
            width: 810,
            buttonAlign: 'left',
            buttonLeftMargin: 150,
            labelWidth: 230,
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
                id: 'EvnUslugaOnkoSurgEditForm',
                bodyStyle:'background:#DFE8F6;padding:5px;',
                border: false,
                labelWidth: 230,
                collapsible: true,
                labelAlign: 'right',
                region: 'center',
                url:'/?c=EvnUslugaOnkoSurg&m=save',
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
					bodyStyle: 'background: #DFE8F6;',
					border: false,
					layout: 'column',
					items: [{
						bodyStyle: 'background: #DFE8F6;',
						border: false,
						layout: 'form',
						items: [{
							fieldLabel: langs('Дата начала'),
							name: 'EvnUslugaOnkoSurg_setDate',
                    allowBlank: false,
                    xtype: 'swdatefield',
                    listeners: {
                        'change': function(field){
                           thas.setUslugaFieldsParams();
                           thas.setUslugaComplexFilter();
                        }
                    },
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
							name: 'EvnUslugaOnkoSurg_setTime',
							onTriggerClick: function() {
								var time_field = thas.form.findField('EvnUslugaOnkoSurg_setTime');

								if ( time_field.disabled ) {
									return false;
								}

								setCurrentDateTime({
									callback: function() {
										thas.form.findField('EvnUslugaOnkoSurg_setDate').fireEvent('change', thas.form.findField('EvnUslugaOnkoSurg_setDate'), thas.form.findField('EvnUslugaOnkoSurg_setDate').getValue());
									},
									dateField: thas.form.findField('EvnUslugaOnkoSurg_setDate'),
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
							name: 'EvnUslugaOnkoSurg_disDate',
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
							name: 'EvnUslugaOnkoSurg_disTime',
							onTriggerClick: function() {
								var time_field = thas.form.findField('EvnUslugaOnkoSurg_disTime');

								if ( time_field.disabled ) {
									return false;
								}

								setCurrentDateTime({
									dateField: thas.form.findField('EvnUslugaOnkoSurg_disDate'),
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
                    fieldLabel: langs('Место выполнения'),
                    autoLoad: true,
                    hiddenName: 'Lpu_uid',
                    allowBlank: !getRegionNick().inlist([ 'kareliya', 'perm', 'ufa' ]),
                    xtype: 'swlpulocalcombo',
                    width: 400,
                    listeners: {
                        change: function(combo, newValue){
                            var mp_combo = thas.form.findField('MedPersonal_id');
                            var mp_combo_value = mp_combo.getValue();
                            var on_date = '';
                            var set_dt = thas.form.findField('EvnUslugaOnkoSurg_setDate').getValue();
                            if (set_dt) {
                                on_date = Ext.util.Format.date(set_dt, 'd.m.Y');
                            }

							//thas.setUslugaComplexFilter();

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
					//changeDisabled: (getRegionNick().inlist(['ufa'])),
					comboSubject: 'UslugaCategory',
                    fieldLabel: lang['kategoriya_uslugi'],
                    hiddenName: 'UslugaCategory_id',
					lastQuery: '',
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
                    width: 400,
                    xtype: 'swcommonsprcombo'
                }, {
                    allowBlank: false,
                    fieldLabel: lang['nazvanie_operatsii'],
                    hiddenName: 'UslugaComplex_id',
					to: 'EvnUslugaOnkoSurg',
                    listWidth: 500,
                    width: 400,
                    xtype: 'swuslugacomplexnewcombo'
                },{
                    fieldLabel: lang['tip_operatsii'],
                    hiddenName: 'OperType_id',
                    xtype: 'swcommonsprlikecombo',
                    allowBlank: true,
                    sortField:'OperType_Code',
                    comboSubject: 'OperType',
                    width: 400
                }, {
                    fieldLabel: lang['kto_provodil'],
                    autoLoad: false,
					ctxSerach: true,
					editable: true,
                    hiddenName: 'MedPersonal_id',
                    allowBlank: true,
                    xtype: 'swmedpersonalallcombo',
                    anchor: null,
                    width: 400
                }, {
                    fieldLabel: lang['uslovie_provedeniya_lecheniya'],
                    comboSubject: 'TreatmentConditionsType',
                    allowBlank: true,
                    xtype: 'swcommonsprlikecombo',
                    width: 400
                }, {
                    fieldLabel: lang['harakter_hirurgicheskogo_lecheniya'],
                    hiddenName: 'OnkoSurgTreatType_id',
                    xtype: 'swcommonsprlikecombo',
                    sortField:'OnkoSurgTreatType_Code',
                    comboSubject: 'OnkoSurgTreatType',
                    width: 400
                }, {
					allowBlank: false,
                    comboSubject: 'OnkoSurgicalType',
                    fieldLabel: langs('Тип лечения'),
                    hiddenName: 'OnkoSurgicalType_id',
					moreFields: [
						{name: 'OnkoSurgicalType_begDate', type: 'date', dateFormat: 'd.m.Y' },
						{name: 'OnkoSurgicalType_endDate', type: 'date', dateFormat: 'd.m.Y' }
					],
                    width: 400,
                    xtype: 'swcommonsprlikecombo'
                }, 
                this.AggTypePanel,
                this.AggTypePanel2
                ]
            }],
            reader: new Ext.data.JsonReader({
                success: Ext.emptyFn
            }, [
                {name: 'EvnUslugaOnkoSurg_pid'},
                {name: 'Server_id'},
                {name: 'PersonEvn_id'},
                {name: 'Person_id'},
                {name: 'EvnUslugaOnkoSurg_setDate'},
                {name: 'EvnUslugaOnkoSurg_setTime'},
                {name: 'EvnUslugaOnkoSurg_disDate'},
                {name: 'EvnUslugaOnkoSurg_disTime'},
                {name: 'Morbus_id'},
                {name: 'Lpu_uid'},
                {name: 'EvnUslugaOnkoSurg_id'},
                {name: 'UslugaCategory_id'},
                {name: 'UslugaComplex_id'},
                {name: 'OperType_id'},
                {name: 'OnkoSurgTreatType_id'},
                {name: 'OnkoSurgicalType_id'},
                {name: 'OnkoUslugaBeamMethodType_id'},
                {name: 'OnkoUslugaBeamRadioModifType_id'},
                {name: 'OnkoUslugaBeamFocusType_id'},
                {name: 'OnkoPlanType_id'},
                {name: 'AggType_id'},
                {name: 'TreatmentConditionsType_id'},
                {name: 'EvnUslugaOnkoSurg_TotalDoseTumor'},
                {name: 'OnkoUslugaBeamUnitType_id'},
                {name: 'EvnUslugaOnkoSurg_TotalDoseRegZone'},
                {name: 'OnkoUslugaBeamUnitType_did'}
            ]),
            url: '/?c=EvnUslugaOnkoSurg&m=save'
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
        sw.Promed.swEvnUslugaOnkoSurgEditWindow.superclass.initComponent.apply(this, arguments);
        this.form = this.findById('EvnUslugaOnkoSurgEditForm').getForm();
    }
});
