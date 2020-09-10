/**
 * swEvnUslugaOnkoGormunEditWindow - окно редактирования "Гормоноиммунотерапевтическое лечение"
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
sw.Promed.swEvnUslugaOnkoGormunEditWindow = Ext.extend(sw.Promed.BaseForm, {
    winTitle: lang['gormonoimmunoterapevticheskoe_lechenie'],
    action: null,
	actionAdd: false,
    buttonAlign: 'left',
    callback: Ext.emptyFn,
    closable: true,
    closeAction: 'hide',
    draggable: true,
    formMode: 'remote',
    formStatus: 'edit',
    layout: 'border',
    modal: true,
    width: 750,
    height: 750,
    listeners: {
        hide: function() {
            this.onHide();
        }
    },
	onCancelAction: function() {
		var EvnUslugaOnkoGormun_id = this.form.findField('EvnUslugaOnkoGormun_id').getValue();

		if ( !Ext.isEmpty(EvnUslugaOnkoGormun_id) && this.actionAdd == true ) {
			// удалить услугу
			// закрыть окно после успешного удаления
			var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Удаление лечения..."});
			loadMask.show();

			Ext.Ajax.request({
				callback: function(options, success, response) {
					loadMask.hide();

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
    onHide: Ext.emptyFn,
    doSave:  function(options) {
        var thas = this;
        if ( !this.form.isValid() )
        {
            sw.swMsg.show(
                {
                    buttons: Ext.Msg.OK,
                    fn: function()
                    {
                        thas.findById('EvnUslugaOnkoGormunEditForm').getFirstInvalidEl().focus(true);
                    },
                    icon: Ext.Msg.WARNING,
                    msg: ERR_INVFIELDS_MSG,
                    title: ERR_INVFIELDS_TIT
                });
            return false;
        }
        var formParams = this.form.getValues();
        if (!formParams.EvnUslugaOnkoGormun_IsBeam &&
            !formParams.EvnUslugaOnkoGormun_IsSurg &&
            !formParams.EvnUslugaOnkoGormun_IsDrug &&
            !formParams.EvnUslugaOnkoGormun_IsOther
        ) {
            sw.swMsg.show({
                buttons: Ext.Msg.OK,
                fn: function() {
                    thas.form.findField('EvnUslugaOnkoGormun_IsBeam').focus(true);
                },
                icon: Ext.Msg.WARNING,
                msg: lang['obyazatelno_vyibrat_hotya_byi_odin_vid_gormonoimmunoterapii'],
                title: ERR_INVFIELDS_TIT
            });
            return false;
        }

        if (!options || Ext.isEmpty(options.no_mod_check)) {
            var mod_exists = false;
            this.MorbusOnkoDrugFrame.getGrid().getStore().each(function(record) {
                if(record.get('MorbusOnkoDrug_id') > 0) {
                    mod_exists = true;
                    return false;
                }
            });
            if (!mod_exists && formParams.EvnUslugaOnkoGormun_IsDrug) { //нет записей в разделе "Препарат", Вид гормоноиммунотерапии - Лекарственная
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

        this.submit();
        return true;
    },
    submit: function() {
        var thas = this;
        var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение..."});
        loadMask.show();
        var formParams = this.form.getValues();
        formParams.EvnUslugaOnkoGormun_IsBeam = (formParams.EvnUslugaOnkoGormun_IsBeam)?2:1;
        formParams.EvnUslugaOnkoGormun_IsSurg = (formParams.EvnUslugaOnkoGormun_IsSurg)?2:1;
        formParams.EvnUslugaOnkoGormun_IsDrug = (formParams.EvnUslugaOnkoGormun_IsDrug)?2:1;
        formParams.EvnUslugaOnkoGormun_IsOther = (formParams.EvnUslugaOnkoGormun_IsOther)?2:1;
		if (this.EvnPL_id)
			formParams.EvnPL_id = this.EvnPL_id;
        var AggTypes = this.AggTypePanel.getValues();
        formParams.AggTypes = (AggTypes.length > 1 ? AggTypes.join(',') : AggTypes);
        Ext.Ajax.request({
            failure:function () {
                loadMask.hide();
            },
            params: formParams,
            method: 'POST',
            success: function (result) {
                loadMask.hide();
                var response = Ext.util.JSON.decode(result.responseText);
				if (response.EvnUslugaCommon_id) {
					if (response.EvnUslugaCommon_id == -1) {
						showSysMsg('Добавленная услуга уже имеется в текущем случае лечения и не будет внесена повторно');
					}
					else {
						showSysMsg('Вы добавили новую услугу. Услуга скопирована в раздел "Услуги" текущего посещения / движения');
					}
				}
                if (!response.success) {
                    //сообщение уже выведено
                } else if (typeof thas.onSave == 'function') {
                    thas.onSave(response.EvnUslugaOnkoGormun_id);
                    thas.form.findField('EvnUslugaOnkoGormun_id').setValue(response.EvnUslugaOnkoGormun_id);
                    thas.action = 'edit';
                    thas.setTitle(thas.winTitle +lang['_redaktirovanie']);
                } else {
                    formParams.EvnUslugaOnkoGormun_id = response.EvnUslugaOnkoGormun_id;
                    thas.callback(formParams);
                    thas.hide();
                }
            },
            url:'/?c=EvnUslugaOnkoGormun&m=save'
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
        form.MorbusOnkoDrugFrame.setReadOnly(d);
        form.buttons[0].setDisabled(d);
    },
    openMorbusOnkoDrugWindow: function(action) {
        if (!action || !action.toString().inlist(['add', 'edit', 'view'])) {
            return false;
        }

        if (getWnd('swMorbusOnkoDrugWindow').isVisible()) {
            getWnd('swMorbusOnkoDrugWindow').hide();
        }

        var thas = this;
        var win = this;
        var grid = this.MorbusOnkoDrugFrame.getGrid();
        var selected_record = grid.getSelectionModel().getSelected();
        var params = {
            EvnUsluga_setDT: this.form.findField('EvnUslugaOnkoGormun_setDate').getValue(),
            EvnUsluga_disDT: this.form.findField('EvnUslugaOnkoGormun_disDate').getValue()
        };
        params.action = action;
        params.callback = function(data) {
            //thas.MorbusOnkoDrugFrame.ViewActions.action_refresh.execute();
            grid.getStore().load({
                params: {Evn_id: data.Evn_id},
                globalFilters: {Evn_id: data.Evn_id}
            });
            win.onSaveDrug();
        };
        if (action == 'add') {
            this.onSave = null;
            var evn_id = thas.form.findField('EvnUslugaOnkoGormun_id').getValue();
            if (evn_id) {
                params.formParams = {
                    MorbusOnko_id: thas.form.findField('MorbusOnko_id').getValue(),
                    Evn_id: evn_id,
                    MorbusOnkoDrug_begDT: thas.form.findField('EvnUslugaOnkoGormun_setDate').getValue(),
                    MorbusOnkoVizitPLDop_id: this.MorbusOnkoVizitPLDop_id,
                    MorbusOnkoLeave_id: this.MorbusOnkoLeave_id,
                    MorbusOnkoDiagPLStom_id: this.MorbusOnkoDiagPLStom_id
                };
                getWnd('swMorbusOnkoDrugWindow').show(params);
            } else {
                this.onSave = function(evn_id){
                    params.formParams = {
                        MorbusOnko_id: thas.form.findField('MorbusOnko_id').getValue(),
                        Evn_id: evn_id,
                        MorbusOnkoDrug_begDT: thas.form.findField('EvnUslugaOnkoGormun_setDate').getValue(),
                        MorbusOnkoVizitPLDop_id: this.MorbusOnkoVizitPLDop_id,
                        MorbusOnkoLeave_id: this.MorbusOnkoLeave_id,
                        MorbusOnkoDiagPLStom_id: this.MorbusOnkoDiagPLStom_id
                    };
                    getWnd('swMorbusOnkoDrugWindow').show(params);
                    thas.onSave = null;
                };
                this.doSave({no_mod_check: true});
            }
        } else {
            if (!selected_record) {
                return false;
            }
            params.formParams = selected_record.data;
            params.onHide = function() {
                grid.getView().focusRow(grid.getStore().indexOf(selected_record));
            };
            getWnd('swMorbusOnkoDrugWindow').show(params);
        }
        return true;
    },
    openMorbusOnkoDrugSelectWindow: function() {
        var wnd = this;
        var grid = this.MorbusOnkoDrugFrame.getGrid();

        var params = {
            MorbusOnko_id: this.form.findField('MorbusOnko_id').getValue(),
            Evn_id: this.form.findField('EvnUslugaOnkoGormun_id').getValue()
        };

        params.callback = function() {
            grid.getStore().load({
                params: {Evn_id: params.Evn_id},
                globalFilters: {Evn_id: params.Evn_id}
            });
        };

        if (params.Evn_id) {
            getWnd('swMorbusOnkoDrugSelectWindow').show(params);
        } else {
            this.onSave = function(evn_id){
                params.Evn_id = evn_id;
                getWnd('swMorbusOnkoDrugSelectWindow').show(params);
                wnd.onSave = null;
            };
            this.doSave({no_mod_check: true});
        }
    },
    disabledDatePeriods: null,
    setAllowedDates: function() {
        var that = this;
        var set_dt_field = that.form.findField('EvnUslugaOnkoGormun_setDate');
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
        var set_dt_field = that.form.findField('EvnUslugaOnkoGormun_setDate');
        var set_dt_value = null;
        if (!Ext.isEmpty(set_dt_field.getValue())) {
            set_dt_value = set_dt_field.getValue().format('d.m.Y');
        }
        var dis_dt_field = that.form.findField('EvnUslugaOnkoGormun_disDate');

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
                (!rec.get('OnkoRadiotherapy_begDate') || rec.get('OnkoRadiotherapy_begDate') <= EvnUslugaOnkoGormun_setDate)
                && (!rec.get('OnkoRadiotherapy_endDate') || rec.get('OnkoRadiotherapy_endDate') >= EvnUslugaOnkoGormun_setDate)
            )
        });

        base_form.findField('OnkoRadiotherapy_id').setBaseFilter(function(rec) {
            return (
                (!rec.get('OnkoRadiotherapy_begDate') || rec.get('OnkoRadiotherapy_begDate') <= EvnUslugaOnkoGormun_setDate)
                && (!rec.get('OnkoRadiotherapy_endDate') || rec.get('OnkoRadiotherapy_endDate') >= EvnUslugaOnkoGormun_setDate)
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
			UslugaComplex_Date = base_form.findField('EvnUslugaOnkoGormun_setDate').getValue();

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
        sw.Promed.swEvnUslugaOnkoGormunEditWindow.superclass.show.apply(this, arguments);
        this.action = '';
		this.actionAdd = false;
        this.callback = Ext.emptyFn;
        this.EvnUslugaOnkoGormun_id = null;
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
        if ( arguments[0].onSaveDrug && typeof arguments[0].onSaveDrug == 'function') {
            this.onSaveDrug = arguments[0].onSaveDrug;
        } else {
            this.onSaveDrug = function() {};
        }
        if ( arguments[0].owner ) {
            this.owner = arguments[0].owner;
        }
        if ( arguments[0].EvnUslugaOnkoGormun_id ) {
            this.EvnUslugaOnkoGormun_id = arguments[0].EvnUslugaOnkoGormun_id;
        }
		if (!Ext.isEmpty(arguments[0].formParams.EvnPL_id))
			this.EvnPL_id = arguments[0].formParams.EvnPL_id;

		this.MorbusOnkoVizitPLDop_id = arguments[0].MorbusOnkoVizitPLDop_id || null;
        this.MorbusOnkoLeave_id = arguments[0].MorbusOnkoLeave_id || null;
        this.MorbusOnkoDiagPLStom_id = arguments[0].MorbusOnkoDiagPLStom_id || null;
        this.form.reset();
        var grid = this.MorbusOnkoDrugFrame.getGrid();
        grid.getStore().removeAll();

        if (!this.MorbusOnkoDrugFrame.getAction('add_from_morbus')) {
            this.MorbusOnkoDrugFrame.addActions({
                iconCls: 'add16',
                name: 'add_from_morbus',
                text: langs('Добавить препарат из специфики'),
                handler: function() {
                    this.openMorbusOnkoDrugSelectWindow();
                }.createDelegate(this)
            });
        }

		this.form.findField('UslugaCategory_id').setContainerVisible(getRegionNick() != 'kz');
		this.form.findField('UslugaComplex_id').setContainerVisible(getRegionNick() != 'kz');

		this.form.findField('UslugaComplex_id').setAllowedUslugaComplexAttributeList([ 'GormImunTerLech' ]);

        switch (arguments[0].action) {
            case 'add':
                this.setTitle(this.winTitle +lang['_dobavlenie']);
                this.setFieldsDisabled(false);
				this.actionAdd = true;
                break;
            case 'edit':
                this.setTitle(this.winTitle +lang['_redaktirovanie']);
                this.setFieldsDisabled(false);
                break;
            case 'view':
                this.setTitle(this.winTitle +lang['_prosmotr']);
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
                //thas.form.findField('EvnUslugaOnkoGormun_setDate').setValue(getGlobalOptions().date);
                loadMask.hide();
				thas.setAllowedDates();
                this.AggTypePanel.setValues([null]);
				this.form.findField('EvnUslugaOnkoGormun_IsBeam').fireEvent('check', this.form.findField('EvnUslugaOnkoGormun_IsBeam'), this.form.findField('EvnUslugaOnkoGormun_IsBeam').getValue());
				this.form.findField('EvnUslugaOnkoGormun_IsDrug').fireEvent('check', this.form.findField('EvnUslugaOnkoGormun_IsDrug'), this.form.findField('EvnUslugaOnkoGormun_IsDrug').getValue());
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
                        EvnUslugaOnkoGormun_id: thas.EvnUslugaOnkoGormun_id
                    },
                    success: function (response) {
                        var result = Ext.util.JSON.decode(response.responseText);
                        if (result[0]) {
                            result[0].EvnUslugaOnkoGormun_IsBeam = (result[0].EvnUslugaOnkoGormun_IsBeam && result[0].EvnUslugaOnkoGormun_IsBeam == 2);
                            result[0].EvnUslugaOnkoGormun_IsSurg = (result[0].EvnUslugaOnkoGormun_IsSurg && result[0].EvnUslugaOnkoGormun_IsSurg == 2);
                            result[0].EvnUslugaOnkoGormun_IsDrug = (result[0].EvnUslugaOnkoGormun_IsDrug && result[0].EvnUslugaOnkoGormun_IsDrug == 2);
                            result[0].EvnUslugaOnkoGormun_IsOther = (result[0].EvnUslugaOnkoGormun_IsOther && result[0].EvnUslugaOnkoGormun_IsOther == 2);
                            thas.form.setValues(result[0]);
                            thas.InformationPanel.load({
                                Person_id: result[0].Person_id
                            });
                            if(result[0].AggTypes){
                                thas.AggTypePanel.setValues(result[0].AggTypes);
                            } else {
                                thas.AggTypePanel.setValues([null]);
                            }
                            loadMask.hide();
                            grid.getStore().load({
                                params: {Evn_id: result[0].EvnUslugaOnkoGormun_id},
                                globalFilters: {Evn_id: result[0].EvnUslugaOnkoGormun_id}
                            });

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

							thas.form.findField('EvnUslugaOnkoGormun_IsBeam').fireEvent('check', thas.form.findField('EvnUslugaOnkoGormun_IsBeam'), thas.form.findField('EvnUslugaOnkoGormun_IsBeam').getValue());
							thas.form.findField('EvnUslugaOnkoGormun_IsDrug').fireEvent('check', thas.form.findField('EvnUslugaOnkoGormun_IsDrug'), thas.form.findField('EvnUslugaOnkoGormun_IsDrug').getValue());
                        }
                    },
                    url:'/?c=EvnUslugaOnkoGormun&m=load'
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

        this.MorbusOnkoDrugFrame = new sw.Promed.ViewFrame({
            id: 'OnkoGormunDrug',
            title: lang['2_preparat'],
            collapsible: true,
            actions: [
                {name: 'action_add', handler: function() {
                    thas.openMorbusOnkoDrugWindow('add');
                }},
                {name: 'action_edit', handler: function() {
                    thas.openMorbusOnkoDrugWindow('edit');
                }},
                {name: 'action_view', handler: function() {
                    thas.openMorbusOnkoDrugWindow('view');
                }},
                {name: 'action_delete'},
                {name: 'action_print'}
            ],
            autoExpandColumn: 'autoexpand',
            autoExpandMin: 150,
            autoLoadData: false,
            border: true,
            dataUrl: '/?c=MorbusOnkoDrug&m=readList',
            paging: false,
            object: 'MorbusOnkoDrug',
            obj_isEvn: false,
            stringfields: [
                {name: 'MorbusOnkoDrug_id', type: 'int', header: 'ID', key: true},
                {name: 'MorbusOnko_id', type: 'int', hidden: true},
                {name: 'Evn_id', type: 'int', hidden: true},
                {name: 'MorbusOnkoDrug_begDT', type: 'date', hidden: true},
                {name: 'MorbusOnkoDrug_endDT', type: 'date', hidden: true},
                {name: 'MorbusOnkoDrug_DatePeriod', header: lang['prodoljitelnost'], width: 200, renderer: function(v, p, record){
                    if (!record.get('MorbusOnkoDrug_begDT')) {
                        return '';
                    }
                    var period = record.get('MorbusOnkoDrug_begDT').format('d.m.Y');
                    if (record.get('MorbusOnkoDrug_endDT')) {
                        period += ' - '+ record.get('MorbusOnkoDrug_endDT').format('d.m.Y');
                    }
                    return period;
                }},
                {name: 'OnkoDrug_Name', type: 'string', header: lang['preparat'], id: 'autoexpand'},
                {name: 'MorbusOnkoDrug_SumDose', type: 'string', header: lang['summarnaya_doza'], width: 100}
            ],
            toolbar: true
        });

        this.AggTypePanel = new sw.Promed.AddOnkoComplPanel({
            objectName: 'AggType',
            fieldLabelTitle: lang['oslojnenie'],
            win: this,
            width: 670,
            buttonAlign: 'left',
            buttonLeftMargin: 150,
            labelWidth: 200,
            fieldWidth: 300,
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
                title: lang['1_lechenie'],
                xtype: 'form',
                autoHeight: true,
                id: 'EvnUslugaOnkoGormunEditForm',
                bodyStyle:'background:#DFE8F6;padding:5px;',
                border: false,
                labelWidth: 200,
                collapsible: true,
                labelAlign: 'right',
                region: 'center',
                url:'/?c=EvnUslugaOnkoGormun&m=save',
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
					bodyStyle: 'background: #DFE8F6;',
					border: false,
					layout: 'column',
					items: [{
						bodyStyle: 'background: #DFE8F6;',
						border: false,
						layout: 'form',
						items: [{
							fieldLabel: langs('Дата начала'),
							name: 'EvnUslugaOnkoGormun_setDate',
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
							name: 'EvnUslugaOnkoGormun_setTime',
							onTriggerClick: function() {
								var time_field = thas.form.findField('EvnUslugaOnkoGormun_setTime');

								if ( time_field.disabled ) {
									return false;
								}

								setCurrentDateTime({
									callback: function() {
										thas.form.findField('EvnUslugaOnkoGormun_setDate').fireEvent('change', thas.form.findField('EvnUslugaOnkoGormun_setDate'), thas.form.findField('EvnUslugaOnkoGormun_setDate').getValue());
									},
									dateField: thas.form.findField('EvnUslugaOnkoGormun_setDate'),
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
							name: 'EvnUslugaOnkoGormun_disDate',
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
							name: 'EvnUslugaOnkoGormun_disTime',
							onTriggerClick: function() {
								var time_field = thas.form.findField('EvnUslugaOnkoGormun_disTime');

								if ( time_field.disabled ) {
									return false;
								}

								setCurrentDateTime({
									dateField: thas.form.findField('EvnUslugaOnkoGormun_disDate'),
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
					width: 300,
					xtype: 'swuslugacategorycombo'
				}, {
					allowBlank: !getRegionNick().inlist([ 'astra', 'kareliya', 'penza', 'perm', 'ufa' ]),
					fieldLabel: langs('Название услуги'),
					hiddenName: 'UslugaComplex_id',
					listWidth: 700,
					to: 'EvnUslugaOnkoGormun',
					width: 450,
					xtype: 'swuslugacomplexnewcombo'
				}, {
                    title: langs('Вид гормоноиммунотерапии'),
                    xtype: 'fieldset',
                    anchor: '100%',
                    autoHeight: true,
                    style: 'padding:0px; margin-bottom:5px;',
                    layout: 'column',
                    items: [{
                        layout: 'form',
                        width: '50%',
                        bodyStyle:'background:#DFE8F6; margin-left:30px;',
                        border: false,
                        labelWidth: 100,
                        items: [{
                            fieldLabel: lang['lekarstvennaya'],
                            name: 'EvnUslugaOnkoGormun_IsDrug',
                            anchor: '100%',
                            xtype: 'checkbox',
                            listeners: {
                                check: function(checkbox,checked){
                                    if (checked) {
                                        thas.form.findField('EvnUslugaOnkoGormun_IsOther').setValue(false);
                                    }

									if ( getRegionNick() != 'kz' ) {
										thas.form.findField('DrugTherapyLineType_id').setAllowBlank(!checked || thas.form.findField('EvnUslugaOnkoGormun_IsBeam').getValue() == true || thas.form.findField('EvnUslugaOnkoGormun_IsSurg').getValue() == true);
										thas.form.findField('DrugTherapyLineType_id').setContainerVisible(checked);
										thas.form.findField('DrugTherapyLoopType_id').setAllowBlank(!checked || thas.form.findField('EvnUslugaOnkoGormun_IsBeam').getValue() == true || thas.form.findField('EvnUslugaOnkoGormun_IsSurg').getValue() == true);
										thas.form.findField('DrugTherapyLoopType_id').setContainerVisible(checked);

										if ( !checked ) {
											thas.form.findField('DrugTherapyLineType_id').clearValue();
											thas.form.findField('DrugTherapyLoopType_id').clearValue();
										}
									}
                                }
                            }
                        }, {
                            fieldLabel: lang['luchevaya'],
                            name: 'EvnUslugaOnkoGormun_IsBeam',
                            anchor: '100%',
                            xtype: 'checkbox',
                            listeners: {
                                check: function(checkbox,checked){
                                    if (checked) {
                                        thas.form.findField('EvnUslugaOnkoGormun_IsOther').setValue(false);
                                    }

                                    thas.form.findField('OnkoRadiotherapy_id').setAllowBlank(!getRegionNick().inlist(['astra', 'adygeya']) || !checked);
                                    thas.form.findField('OnkoRadiotherapy_id').setContainerVisible(checked);
									thas.form.findField('EvnUslugaOnkoGormun_CountFractionRT').setAllowBlank(!checked);
									thas.form.findField('EvnUslugaOnkoGormun_CountFractionRT').setContainerVisible(checked);
									thas.form.findField('EvnUslugaOnkoGormun_TotalDoseTumor').setAllowBlank(!getRegionNick().inlist(['astra', 'adygeya']) || thas.form.findField('EvnUslugaOnkoGormun_IsDrug').getValue() == true || !checked);
									thas.form.findField('EvnUslugaOnkoGormun_TotalDoseTumor').setContainerVisible(checked);
									thas.form.findField('EvnUslugaOnkoGormun_TotalDoseRegZone').setAllowBlank(getRegionNick() != 'astra' || !checked || thas.form.findField('EvnUslugaOnkoGormun_IsDrug').getValue() == true);
									thas.form.findField('EvnUslugaOnkoGormun_TotalDoseRegZone').setContainerVisible(checked);

									thas.form.findField('DrugTherapyLineType_id').setAllowBlank(thas.form.findField('EvnUslugaOnkoGormun_IsDrug').getValue() == false || checked || thas.form.findField('EvnUslugaOnkoGormun_IsSurg').getValue() == true);
									thas.form.findField('DrugTherapyLoopType_id').setAllowBlank(thas.form.findField('EvnUslugaOnkoGormun_IsDrug').getValue() == false || checked || thas.form.findField('EvnUslugaOnkoGormun_IsSurg').getValue() == true);

									if ( !checked ) {
                                        thas.form.findField('OnkoRadiotherapy_id').clearValue();
										thas.form.findField('EvnUslugaOnkoGormun_CountFractionRT').setValue(null);
										thas.form.findField('EvnUslugaOnkoGormun_TotalDoseTumor').setValue(null);
										thas.form.findField('EvnUslugaOnkoGormun_TotalDoseRegZone').setValue(null);
									}
                                }
                            }
                        }]
                    }, {
                        layout: 'form',
                        width: '50%',
                        bodyStyle:'background:#DFE8F6;',
                        border: false,
                        labelWidth: 100,
                        items: [{
                            fieldLabel: lang['hirurgicheskaya'],
                            name: 'EvnUslugaOnkoGormun_IsSurg',
                            anchor: '100%',
                            xtype: 'checkbox',
                            listeners: {
                                check: function(checkbox,checked){
                                    if (checked) {
                                        thas.form.findField('EvnUslugaOnkoGormun_IsOther').setValue(false);
                                    }

									thas.form.findField('DrugTherapyLineType_id').setAllowBlank(thas.form.findField('EvnUslugaOnkoGormun_IsDrug').getValue() == false || thas.form.findField('EvnUslugaOnkoGormun_IsBeam').getValue() == true || checked);
									thas.form.findField('DrugTherapyLoopType_id').setAllowBlank(thas.form.findField('EvnUslugaOnkoGormun_IsDrug').getValue() == false || thas.form.findField('EvnUslugaOnkoGormun_IsBeam').getValue() == true || checked);
                                }
                            }
                        }, {
                            fieldLabel: lang['neizvestno'],
                            name: 'EvnUslugaOnkoGormun_IsOther',
                            anchor: '100%',
                            xtype: 'checkbox',
                            listeners: {
                                check: function(checkbox,checked){
                                    if (checked) {
                                        thas.form.findField('EvnUslugaOnkoGormun_IsBeam').setValue(false);
                                        thas.form.findField('EvnUslugaOnkoGormun_IsSurg').setValue(false);
                                        thas.form.findField('EvnUslugaOnkoGormun_IsDrug').setValue(false);
                                    }
                                }
                            }
                        }]
                    }]
                }, {
                    fieldLabel: lang['preimuschestvennaya_napravlennost'],
                    hiddenName: 'OnkoUslugaGormunFocusType_id',
                    xtype: 'swcommonsprlikecombo',
                    allowBlank: false,
                    sortField:'OnkoUslugaGormunFocusType_Code',
                    comboSubject: 'OnkoUslugaGormunFocusType',
                    width: 300
                }, {
                    comboSubject: 'OnkoRadiotherapy',
                    fieldLabel: langs('Тип лечения'),
                    moreFields: [
                        {name: 'OnkoRadiotherapy_begDate', type: 'date', dateFormat: 'd.m.Y' },
                        {name: 'OnkoRadiotherapy_endDate', type: 'date', dateFormat: 'd.m.Y' }
                    ],
                    width: 300,
                    xtype: 'swcommonsprlikecombo'
                }, {
                    fieldLabel: langs('Место выполнения'),
                    autoLoad: true,
                    hiddenName: 'Lpu_uid',
                    allowBlank: !getRegionNick().inlist([ 'kareliya', 'perm', 'ufa' ]),
                    xtype: 'swlpulocalcombo',
                    width: 300
				}, {
					comboSubject: 'OnkoTreatType',
					fieldLabel: lang['harakter_lecheniya'],
					hiddenName: 'OnkoTreatType_id',
					sortField:'OnkoTreatType_Code',
					width: 300,
					xtype: 'swcommonsprlikecombo'
                }, {
                    fieldLabel: lang['uslovie_provedeniya_lecheniya'],
                    comboSubject: 'TreatmentConditionsType',
                    xtype: 'swcommonsprlikecombo',
                    width: 300
                }, 
                this.AggTypePanel,
				{
                    fieldLabel: langs('Линия лекарственной терапии'),
                    comboSubject: 'DrugTherapyLineType',
                    xtype: 'swcommonsprlikecombo',
                    width: 300
                }, {
                    fieldLabel: langs('Цикл лекарственной терапии'),
                    comboSubject: 'DrugTherapyLoopType',
                    xtype: 'swcommonsprlikecombo',
                    width: 300
                }, {
					allowBlank: false,
					allowDecimals: false,
					allowNegative: false,
					fieldLabel: langs('Кол-во фракций проведения лучевой терапии'),
					name: 'EvnUslugaOnkoGormun_CountFractionRT',
					width: 80,
					xtype: 'numberfield'
				}, {
					allowBlank: false,
					allowDecimals: true,
					allowNegative: false,
                    decimalPrecision: 3,
                    fieldLabel: langs('Суммарная доза облучения опухоли'),
					name: 'EvnUslugaOnkoGormun_TotalDoseTumor',
                    autoCreate: {tag: "input", maxLength: "8", autocomplete: "off"},
					width: 80,
					xtype: 'numberfield'
				}, {
					allowBlank: false,
					allowDecimals: true,
					allowNegative: false,
                    decimalPrecision: 3,
                    fieldLabel: langs('Суммарная доза облучения зон регионарного метастазирования'),
					name: 'EvnUslugaOnkoGormun_TotalDoseRegZone',
                    autoCreate: {tag: "input", maxLength: "8", autocomplete: "off"},
					width: 80,
					xtype: 'numberfield'
				}]
            }, this.MorbusOnkoDrugFrame],
            reader: new Ext.data.JsonReader({
                success: Ext.emptyFn
            }, [
                {name: 'EvnUslugaOnkoGormun_id'},
                {name: 'EvnUslugaOnkoGormun_pid'},
                {name: 'Server_id'},
                {name: 'Person_id'},
                {name: 'PersonEvn_id'},
                {name: 'MorbusOnko_id'},
                {name: 'Morbus_id'},
                {name: 'EvnUslugaOnkoGormun_setDate'},
                {name: 'EvnUslugaOnkoGormun_setTime'},
                {name: 'EvnUslugaOnkoGormun_disDate'},
                {name: 'EvnUslugaOnkoGormun_disTime'},
                {name: 'Lpu_uid'},
                {name: 'EvnUslugaOnkoGormun_CountFractionRT'},
                {name: 'EvnUslugaOnkoGormun_TotalDoseTumor'},
                {name: 'EvnUslugaOnkoGormun_TotalDoseRegZone'},
                {name: 'EvnUslugaOnkoGormun_IsBeam'},
                {name: 'EvnUslugaOnkoGormun_IsSurg'},
                {name: 'EvnUslugaOnkoGormun_IsDrug'},
                {name: 'EvnUslugaOnkoGormun_IsOther'},
                {name: 'OnkoUslugaGormunFocusType_id'},
                {name: 'OnkoRadiotherapy_id'},
                {name: 'OnkoTreatType_id'},
                {name: 'TreatmentConditionsType_id'},
                {name: 'AggType_id'},
				{name: 'DrugTherapyLineType_id'},
				{name: 'DrugTherapyLoopType_id'},
				{name: 'UslugaCategory_id'},
				{name: 'UslugaComplex_id'}
            ]),
            url: '/?c=EvnUslugaOnkoGormun&m=save'
        });
        Ext.apply(this, {
            layout: 'border',
            buttons:
                [{
                    handler: function()
                    {
                        thas.doSave();
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
                            thas.onCancelAction();
                        },
                        iconCls: 'cancel16',
                        text: BTN_FRMCANCEL
                    }],
            items:[this.InformationPanel,form]
        });
        sw.Promed.swEvnUslugaOnkoGormunEditWindow.superclass.initComponent.apply(this, arguments);
        this.form = this.findById('EvnUslugaOnkoGormunEditForm').getForm();
    }
});