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
    buttonAlign: 'left',
    callback: Ext.emptyFn,
    closable: true,
    closeAction: 'hide',
    draggable: true,
    formMode: 'remote',
    formStatus: 'edit',
    layout: 'border',
    modal: true,
    width: 850,
    height: 250,
    autoScroll: true,
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
		if (morbus_id) {
			var loadMask = new Ext.LoadMask(this.form.getEl(), {msg:lang['zagruzka']});
			loadMask.show();
			Ext.Ajax.request({
				failure:function () {
					sw.swMsg.alert(lang['oshibka'], lang['ne_udalos_poluchit_dannyie_s_servera']);
					loadMask.hide();
				},
				params: {
					Morbus_id: morbus_id
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
    show: function() {
        var thas = this;
        var set_dt_field = thas.form.findField('EvnUslugaOnkoSurg_setDate');
        sw.Promed.swEvnUslugaOnkoSurgEditWindow.superclass.show.apply(this, arguments);
        this.action = '';
        this.callback = Ext.emptyFn;
        this.EvnUslugaOnkoSurg_id = null;
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
        this.form.reset();
        
        var usluga_combo = this.form.findField('UslugaComplex_id');

        // Устанавливаем фильтры для услуг
        usluga_combo.clearValue();
        usluga_combo.lastQuery = 'This query sample that is not will never appear';
        usluga_combo.getStore().removeAll();
        usluga_combo.setAllowedUslugaComplexAttributeList([ 'oper' ]);
        usluga_combo.getStore().baseParams.UslugaComplex_Date = null;
        usluga_combo.getStore().baseParams.Lpu_uid = getGlobalOptions().lpu_id;

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
        switch (arguments[0].action) {
            case 'add':
                if (!arguments[0].formParams.EvnUslugaOnkoSurg_setDate) {
                    arguments[0].formParams.EvnUslugaOnkoSurg_setDate = getGlobalOptions().date;
                }
                thas.form.setValues(arguments[0].formParams);

				thas.form.findField('EvnUslugaOnkoSurg_setTime').setValue('00:00');
				thas.form.findField('EvnUslugaOnkoSurg_disTime').setValue('00:00');

                thas.InformationPanel.load({
                    Person_id: arguments[0].formParams.Person_id
                });
                if (set_dt_field.getValue()) {
                    set_dt_field.fireEvent('change', set_dt_field, set_dt_field.getValue(), null);
                }

                if (arguments[0].formParams.EvnUslugaOnkoSurg_pid) {
                    Ext.Ajax.request({
                        failure:function () {
                            loadMask.hide();
							thas.setAllowedDates();
                        },
                        params:{
                            EvnUslugaOnkoSurg_pid: arguments[0].formParams.EvnUslugaOnkoSurg_pid
                        },
                        success: function (response) {
                            loadMask.hide();
							thas.setAllowedDates();
                            var result = Ext.util.JSON.decode(response.responseText);
                        },
                        url:'/?c=EvnUslugaOnkoSurg&m=getDefaultTreatmentConditionsTypeId'
                    });
                } else {
                    loadMask.hide();
					thas.setAllowedDates();
                }
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
                        EvnUslugaOnkoSurg_id: thas.EvnUslugaOnkoSurg_id,
						archiveRecord: thas.archiveRecord
                    },
                    success: function (response) {
                        loadMask.hide();
                        var result = Ext.util.JSON.decode(response.responseText);
                        if (result[0]) {
                            thas.form.setValues(result[0]);
                            thas.InformationPanel.load({
                                Person_id: result[0].Person_id
                            });
							thas.setAllowedDates();
                            var usluga_complex_id = usluga_combo.getValue();
                            set_dt_field.fireEvent('change', set_dt_field, set_dt_field.getValue(), null);

                            if ( !Ext.isEmpty(usluga_complex_id) ) {
                                usluga_combo.getStore().load({
                                    callback: function() {
                                        if ( usluga_combo.getStore().getCount() > 0 ) {
                                            usluga_combo.setValue(usluga_complex_id);
                                        } else {
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
                    xtype: 'hidden'
                }, {
                    name: 'EvnUslugaOnkoSurg_pid',
                    xtype: 'hidden'
				}, {
					name: 'EvnUslugaOnkoSurg_setTime',
					xtype: 'hidden'
				}, {
					name: 'EvnUslugaOnkoSurg_disTime',
					xtype: 'hidden'
                }, {
                    name: 'Morbus_id',
                    xtype: 'hidden'
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
                    fieldLabel: 'күнi (дата)',//lang['data_provedeniya'],
                    name: 'EvnUslugaOnkoSurg_setDate',
                    allowBlank: false,
                    xtype: 'swdatefield',
                    listeners: {
                        change: function(field){
                            var on_date = null;
                            var set_dt = field.getValue();
                            if (typeof set_dt == 'object') {
                                on_date =Ext.util.Format.date(set_dt, 'd.m.Y')
                            } else {
                                on_date = set_dt;
                            }
                            var usluga_combo = thas.form.findField('UslugaComplex_id');
                            usluga_combo.lastQuery = '';
                            usluga_combo.clearValue();
                            usluga_combo.getStore().removeAll();
                            usluga_combo.getStore().baseParams.UslugaComplex_Date = on_date;
                        }
                    },
                    plugins: [new Ext.ux.InputTextMask('99.99.9999', false)]
                }, {
                    allowBlank: false,
                    fieldLabel: lang['nazvanie_operatsii'],
                    hiddenName: 'UslugaComplex_id',
					to: 'EvnUslugaOnkoSurg',
                    listWidth: 500,
                    width: 400,
                    xtype: 'swuslugacomplexnewcombo'
                }
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
                {name: 'UslugaComplex_id'}
            ]),
            url: '/?c=EvnUslugaOnkoSurg&m=save'
        });
        Ext.apply(this, {
            layout: 'border',
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
