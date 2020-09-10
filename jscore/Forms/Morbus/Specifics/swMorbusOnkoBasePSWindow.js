/**
* swMorbusOnkoBasePSWindow - окно редактирования "Госпитализация"
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      MorbusOnko
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @version      06.2013
* @comment      
*/

sw.Promed.swMorbusOnkoBasePSWindow = Ext.extend(sw.Promed.BaseForm, {
    action: null,
    winTitle: lang['gospitalizatsiya'],
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	draggable: true,
	formMode: 'remote',
	formStatus: 'edit',
	layout: 'border',
	modal: true,
	width: 700,
	height: 500,
    maximizable: true,
    autoScroll: true,
	listeners: {
		hide: function() {
			this.onHide();
		}
	},
	onHide: Ext.emptyFn,
	doSave:  function() {
		var that = this;
		if ( !this.form.isValid() )
		{
			sw.swMsg.show(
			{
				buttons: Ext.Msg.OK,
				fn: function() 
				{
					that.findById('MorbusOnkoBasePSEditForm').getFirstInvalidEl().focus(true);
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
		var that = this;
		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение..."});
		loadMask.show();
		var formParams = this.form.getValues();
        formParams.MorbusOnkoBasePS_IsTreatDelay = (formParams.MorbusOnkoBasePS_IsTreatDelay)?2:1;
        formParams.MorbusOnkoBasePS_IsNotTreat = (formParams.MorbusOnkoBasePS_IsNotTreat)?2:1;
        formParams.MorbusOnkoBasePS_IsSurg = (formParams.MorbusOnkoBasePS_IsSurg)?2:1;
        formParams.MorbusOnkoBasePS_IsPreOper = (formParams.MorbusOnkoBasePS_IsPreOper)?2:1;
        formParams.MorbusOnkoBasePS_IsIntraOper = (formParams.MorbusOnkoBasePS_IsIntraOper)?2:1;
        formParams.MorbusOnkoBasePS_IsPostOper = (formParams.MorbusOnkoBasePS_IsPostOper)?2:1;
        formParams.MorbusOnkoBasePS_IsBeam = (formParams.MorbusOnkoBasePS_IsBeam)?2:1;
        formParams.MorbusOnkoBasePS_IsChem = (formParams.MorbusOnkoBasePS_IsChem)?2:1;
        formParams.MorbusOnkoBasePS_IsGormun = (formParams.MorbusOnkoBasePS_IsGormun)?2:1;
        formParams.MorbusOnkoBasePS_IsImmun = (formParams.MorbusOnkoBasePS_IsImmun)?2:1;
        formParams.MorbusOnkoBasePS_IsOther = (formParams.MorbusOnkoBasePS_IsOther)?2:1;
        Ext.Ajax.request({
            failure:function () {
                loadMask.hide();
            },
            params: formParams,
            method: 'POST',
            success: function (result) {
                loadMask.hide();
                if (result.responseText) {
                    var response = Ext.util.JSON.decode(result.responseText);
                    formParams.MorbusOnkoBasePS_id = response.MorbusOnkoBasePS_id;
                    that.callback(formParams);
                    that.hide();
                }
            },
            url:'/?c=MorbusOnkoBasePS&m=save'
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
    onLoadForm: function(formParams) {
        var accessType = formParams.accessType || 'edit';
        this.setFieldsDisabled(this.action == 'view' || accessType == 'view');

        formParams.MorbusOnkoBasePS_IsTreatDelay = (formParams.MorbusOnkoBasePS_IsTreatDelay && formParams.MorbusOnkoBasePS_IsTreatDelay == 2);
        formParams.MorbusOnkoBasePS_IsNotTreat = (formParams.MorbusOnkoBasePS_IsNotTreat && formParams.MorbusOnkoBasePS_IsNotTreat == 2);
        formParams.MorbusOnkoBasePS_IsSurg = (formParams.MorbusOnkoBasePS_IsSurg && formParams.MorbusOnkoBasePS_IsSurg == 2);
        formParams.MorbusOnkoBasePS_IsPreOper = (formParams.MorbusOnkoBasePS_IsPreOper && formParams.MorbusOnkoBasePS_IsPreOper == 2);
        formParams.MorbusOnkoBasePS_IsIntraOper = (formParams.MorbusOnkoBasePS_IsIntraOper && formParams.MorbusOnkoBasePS_IsIntraOper == 2);
        formParams.MorbusOnkoBasePS_IsPostOper = (formParams.MorbusOnkoBasePS_IsPostOper && formParams.MorbusOnkoBasePS_IsPostOper == 2);
        formParams.MorbusOnkoBasePS_IsBeam = (formParams.MorbusOnkoBasePS_IsBeam && formParams.MorbusOnkoBasePS_IsBeam == 2);
        formParams.MorbusOnkoBasePS_IsChem = (formParams.MorbusOnkoBasePS_IsChem && formParams.MorbusOnkoBasePS_IsChem == 2);
        formParams.MorbusOnkoBasePS_IsGormun = (formParams.MorbusOnkoBasePS_IsGormun && formParams.MorbusOnkoBasePS_IsGormun == 2);
        formParams.MorbusOnkoBasePS_IsImmun = (formParams.MorbusOnkoBasePS_IsImmun && formParams.MorbusOnkoBasePS_IsImmun == 2);
        formParams.MorbusOnkoBasePS_IsOther = (formParams.MorbusOnkoBasePS_IsOther && formParams.MorbusOnkoBasePS_IsOther == 2);
        this.form.setValues(formParams);
        var surg_chbox = this.form.findField('MorbusOnkoBasePS_IsSurg');
        surg_chbox.fireEvent('check', surg_chbox, surg_chbox.getValue());
        var lpu_combo = this.form.findField('Lpu_id');
        lpu_combo.fireEvent('change', lpu_combo, lpu_combo.getValue(), null);
    },
    show: function() {
        var that = this;
		sw.Promed.swMorbusOnkoBasePSWindow.superclass.show.apply(this, arguments);		
		this.action = 'add';
		this.callback = Ext.emptyFn;
        if ( !arguments[0] || !arguments[0].formParams || !arguments[0].formParams.Person_id) {
            sw.swMsg.alert(lang['oshibka'], lang['ne_ukazanyi_vhodnyie_dannyie'], function() { that.hide(); });
            return false;
        }
        this.Person_id = arguments[0].formParams.Person_id;
		if ( arguments[0].action ) {
			this.action = arguments[0].action;
		}
		if ( arguments[0].callback && typeof arguments[0].callback == 'function' ) {
			this.callback = arguments[0].callback;
		}
		this.form.reset();
        var ls_combo = this.form.findField('LpuSection_id');
        ls_combo.getStore().clearFilter();
        ls_combo.lastQuery = '';
		
		switch (arguments[0].action) {
			case 'add':
                this.setTitle(this.winTitle +lang['_dobavlenie']);
                this.onLoadForm(arguments[0].formParams);
				break;
			case 'edit':
				this.setTitle(this.winTitle +lang['_redaktirovanie']);
				break;
			case 'view':
				this.setTitle(this.winTitle +lang['_prosmotr']);
				break;
		}
		
        var loadMask = new Ext.LoadMask(this.form.getEl(), {msg:lang['zagruzka']});
        loadMask.show();
		switch (this.action) {
			case 'add':
				that.InformationPanel.load({
					Person_id: this.Person_id
				});
				loadMask.hide();
			break;
			case 'edit':
			case 'view':
				Ext.Ajax.request({
					failure:function () {
						sw.swMsg.alert(lang['oshibka'], lang['ne_udalos_poluchit_dannyie_s_servera']);
						loadMask.hide();
						that.hide();
					},
					params:{
						MorbusOnkoBasePS_id: arguments[0].formParams.MorbusOnkoBasePS_id
					},
                    method: 'POST',
					success: function (response) {
						var result = Ext.util.JSON.decode(response.responseText);
						if (!result[0]) { return false; }

                        that.onLoadForm(result[0]);

						that.InformationPanel.load({
							Person_id: that.Person_id
						});
						loadMask.hide();
                        return true;
					},
					url:'/?c=MorbusOnkoBasePS&m=load'
				});				
			break;	
		}
        return true;
	},
	initComponent: function() {
        var that = this;
		this.InformationPanel = new sw.Promed.PersonInformationPanelShort({
			region: 'north'
		});
        this.formPanel = new Ext.form.FormPanel({
			autoHeight: true,
			autoScroll: true,
			bodyBorder: false,
			border: false,
			frame: false,
            id: 'MorbusOnkoBasePSEditForm',
            bodyStyle:'background:#DFE8F6;padding:5px;',
            labelWidth: 200,
            labelAlign: 'right',
			region: 'center',
			items: [{
                name: 'MorbusOnkoBasePS_id',
                xtype: 'hidden',
                value: 0
            }, {
                name: 'MorbusOnkoBase_id',
                xtype: 'hidden',
                value: 0
            }, {
                name: 'Evn_id',
                xtype: 'hidden',
                value: 0
            }, {
                fieldLabel: lang['data_postupleniya'],
                name: 'MorbusOnkoBasePS_setDT',
                xtype: 'swdatefield',
                allowBlank: false,
                plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
                listeners: {
                    'change':function (field, newValue) {
                        var ls_combo = that.form.findField('LpuSection_id');
                        var lpu_combo = that.form.findField('Lpu_id');
                        var dis_dt_field = that.form.findField('MorbusOnkoBasePS_disDT');

                        var ls_combo_value = ls_combo.getValue();
                        var on_date = '';

                        if (newValue) {
                            dis_dt_field.setMinValue(newValue);
                            on_date = Ext.util.Format.date(newValue, 'd.m.Y');
                        }
                        else {
                            dis_dt_field.setMinValue(null);
                        }

                        ls_combo.lastQuery = '';
                        ls_combo.clearValue();
                        ls_combo.getStore().removeAll();
                        ls_combo.getStore().load({
                            params: {
                                Lpu_id: lpu_combo.getValue(),
                                onDate: on_date,
                                isStac: '2'
                            },
                            callback: function() {
                                var index = ls_combo.getStore().findBy(function(record) {
                                    return ( record.get('LpuSection_id') == ls_combo_value );
                                }.createDelegate(this));
                                var record = ls_combo.getStore().getAt(index);
                                if ( record ) {
                                    ls_combo.setValue(ls_combo_value);
                                    ls_combo.fireEvent('change', ls_combo, ls_combo_value, null);
                                }
                                else {
                                    ls_combo.clearValue();
                                    ls_combo.fireEvent('change', ls_combo, null);
                                }
                            }
                        });
                    }
                }
            }, {
                fieldLabel: lang['data_vyipiski'],
                name: 'MorbusOnkoBasePS_disDT',
                xtype: 'swdatefield',
                plugins: [new Ext.ux.InputTextMask('99.99.9999', false)]
            }, {
                fieldLabel: lang['pervichnaya_povtornaya'],
                hiddenName: 'OnkoHospType_id',
                xtype: 'swcommonsprlikecombo',
                sortField:'OnkoHospType_Code',
                comboSubject: 'OnkoHospType',
                width: 400
            }, {
                fieldLabel: lang['diagnoz'],
                hiddenName: 'Diag_id',
                width: 400,
                xtype: 'swdiagcombo',
				listeners: {
					beforerender: function(combo) {
						combo.autoLoad = true;
						combo.getStore().load();
					}
				}
            }, {
                fieldLabel: lang['tsel_gospitalizatsii'],
                hiddenName: 'OnkoPurposeHospType_id',
                xtype: 'swcommonsprlikecombo',
                allowBlank: false,
                sortField:'OnkoPurposeHospType_Code',
                comboSubject: 'OnkoPurposeHospType',
                width: 400
            }, {
                fieldLabel: lang['mo_provedeniya'],
                width: 400,
                autoLoad: true,
                hiddenName: 'Lpu_id',
                xtype: 'swlpulocalcombo',
                listeners: {
                    change: function(combo, newValue){
                        var ls_combo = that.form.findField('LpuSection_id');
                        var ls_combo_value = ls_combo.getValue();
                        var on_date = '';
                        var set_dt = that.form.findField('MorbusOnkoBasePS_setDT').getValue();
                        if (set_dt) {
                            on_date =Ext.util.Format.date(set_dt, 'd.m.Y')
                        }

                        ls_combo.lastQuery = '';
                        ls_combo.clearValue();
                        ls_combo.getStore().removeAll();
                        ls_combo.getStore().load({
                            params: {
                                Lpu_id: newValue,
                                onDate: on_date,
                                isStac: '2'
                            },
                            callback: function() {
                                var index = ls_combo.getStore().findBy(function(record) {
                                    return ( record.get('LpuSection_id') == ls_combo_value );
                                }.createDelegate(this));
                                var record = ls_combo.getStore().getAt(index);
                                if ( record ) {
                                    ls_combo.setValue(ls_combo_value);
                                    ls_combo.fireEvent('change', ls_combo, ls_combo_value, null);
                                }
                                else {
                                    ls_combo.clearValue();
                                    ls_combo.fireEvent('change', ls_combo, null);
                                }
                            }
                        });
                    }
                }
            }, {
                fieldLabel: lang['otdelenie_statsionara'],
                hiddenName: 'LpuSection_id',
                lastQuery: '',
                listWidth: 650,
                width: 400,
                xtype: 'swlpusectioncombo'
            }, {
                title: lang['provedeno_spetsialnoe_lechenie'],
                xtype: 'fieldset',
                anchor: '100%',
                autoHeight: true,
                style: 'padding:10px;margin:0px;',
                items: [{
                    boxLabel: lang['obsledovanie_lechenie_otsrocheno'],
                    hideLabel: true,
                    name: 'MorbusOnkoBasePS_IsTreatDelay',
                    anchor: '100%',
                    xtype: 'checkbox',
                    listeners: {
                        check: function(checkbox,checked){
                            if (checked) {
                                that.form.findField('MorbusOnkoBasePS_IsNotTreat').setValue(false);
                                that.form.findField('MorbusOnkoBasePS_IsSurg').setValue(false);
                                that.form.findField('MorbusOnkoBasePS_IsBeam').setValue(false);
                                that.form.findField('MorbusOnkoBasePS_IsChem').setValue(false);
                                that.form.findField('MorbusOnkoBasePS_IsGormun').setValue(false);
                                that.form.findField('MorbusOnkoBasePS_IsImmun').setValue(false);
                                that.form.findField('MorbusOnkoBasePS_IsOther').setValue(false);
                            }
                        }
                    }
                }, {
                    boxLabel: lang['obsledovanie_lechenie_ne_predusmotreno'],
                    hideLabel: true,
                    name: 'MorbusOnkoBasePS_IsNotTreat',
                    anchor: '100%',
                    xtype: 'checkbox',
                    listeners: {
                        check: function(checkbox,checked){
                            if (checked) {
                                that.form.findField('MorbusOnkoBasePS_IsTreatDelay').setValue(false);
                                that.form.findField('MorbusOnkoBasePS_IsSurg').setValue(false);
                                that.form.findField('MorbusOnkoBasePS_IsBeam').setValue(false);
                                that.form.findField('MorbusOnkoBasePS_IsChem').setValue(false);
                                that.form.findField('MorbusOnkoBasePS_IsGormun').setValue(false);
                                that.form.findField('MorbusOnkoBasePS_IsImmun').setValue(false);
                                that.form.findField('MorbusOnkoBasePS_IsOther').setValue(false);
                            }
                        }
                    }
                }, {
                    boxLabel: lang['hirurgicheskoe_lechenie_pri_gospitalizatsii'],
                    hideLabel: true,
                    name: 'MorbusOnkoBasePS_IsSurg',
                    anchor: '100%',
                    xtype: 'checkbox',
                    listeners: {
                        check: function(checkbox,checked){
                            that.form.findField('MorbusOnkoBasePS_IsPreOper').setDisabled(!checked);
                            that.form.findField('MorbusOnkoBasePS_IsIntraOper').setDisabled(!checked);
                            that.form.findField('MorbusOnkoBasePS_IsPostOper').setDisabled(!checked);
                            if (checked) {
                                that.form.findField('MorbusOnkoBasePS_IsTreatDelay').setValue(false);
                                that.form.findField('MorbusOnkoBasePS_IsNotTreat').setValue(false);
                                that.form.findField('MorbusOnkoBasePS_IsBeam').setValue(false);
                            } else {
                                that.form.findField('MorbusOnkoBasePS_IsPreOper').setValue(false);
                                that.form.findField('MorbusOnkoBasePS_IsIntraOper').setValue(false);
                                that.form.findField('MorbusOnkoBasePS_IsPostOper').setValue(false);
                            }
                        }
                    }
                }, {
                    xtype: 'panel',
                    border: false,
                    frame: false,
                    bodyStyle:'background:#DFE8F6;',
                    anchor: '100%',
                    autoHeight: true,
                    style: 'margin-left: 30px;',
                    items: [{
                        boxLabel: lang['predoperatsionnaya_luchevaya_terapiya'],
                        hideLabel: true,
                        name: 'MorbusOnkoBasePS_IsPreOper',
                        xtype: 'checkbox',
                        listeners: {
                            check: function(checkbox,checked){
                                if (checked) {
                                    that.form.findField('MorbusOnkoBasePS_IsTreatDelay').setValue(false);
                                    that.form.findField('MorbusOnkoBasePS_IsNotTreat').setValue(false);
                                }
                            }
                        }
                    }, {
                        boxLabel: lang['intraoperatsionnaya_luchevaya_terapiya'],
                        hideLabel: true,
                        name: 'MorbusOnkoBasePS_IsIntraOper',
                        xtype: 'checkbox',
                        listeners: {
                            check: function(checkbox,checked){
                                if (checked) {
                                    that.form.findField('MorbusOnkoBasePS_IsTreatDelay').setValue(false);
                                    that.form.findField('MorbusOnkoBasePS_IsNotTreat').setValue(false);
                                }
                            }
                        }
                    }, {
                        boxLabel: lang['posleoperatsionnaya_luchevaya_terapiya'],
                        hideLabel: true,
                        name: 'MorbusOnkoBasePS_IsPostOper',
                        xtype: 'checkbox',
                        listeners: {
                            check: function(checkbox,checked){
                                if (checked) {
                                    that.form.findField('MorbusOnkoBasePS_IsTreatDelay').setValue(false);
                                    that.form.findField('MorbusOnkoBasePS_IsNotTreat').setValue(false);
                                }
                            }
                        }
                    }]
                }, {
                    boxLabel: lang['luchevaya_terapiya'],
                    hideLabel: true,
                    name: 'MorbusOnkoBasePS_IsBeam',
                    anchor: '100%',
                    xtype: 'checkbox',
                    listeners: {
                        check: function(checkbox,checked){
                            if (checked) {
                                that.form.findField('MorbusOnkoBasePS_IsTreatDelay').setValue(false);
                                that.form.findField('MorbusOnkoBasePS_IsNotTreat').setValue(false);
                                that.form.findField('MorbusOnkoBasePS_IsSurg').setValue(false);
                            }
                        }
                    }
                }, {
                    boxLabel: lang['himioterapiya'],
                    hideLabel: true,
                    name: 'MorbusOnkoBasePS_IsChem',
                    anchor: '100%',
                    xtype: 'checkbox',
                    listeners: {
                        check: function(checkbox,checked){
                            if (checked) {
                                that.form.findField('MorbusOnkoBasePS_IsTreatDelay').setValue(false);
                                that.form.findField('MorbusOnkoBasePS_IsNotTreat').setValue(false);
                            }
                        }
                    }
                }, {
                    boxLabel: lang['gormonoterapiya'],
                    hideLabel: true,
                    name: 'MorbusOnkoBasePS_IsGormun',
                    anchor: '100%',
                    xtype: 'checkbox',
                    listeners: {
                        check: function(checkbox,checked){
                            if (checked) {
                                that.form.findField('MorbusOnkoBasePS_IsTreatDelay').setValue(false);
                                that.form.findField('MorbusOnkoBasePS_IsNotTreat').setValue(false);
                            }
                        }
                    }
                }, {
                    boxLabel: lang['immunoterapiya'],
                    hideLabel: true,
                    name: 'MorbusOnkoBasePS_IsImmun',
                    anchor: '100%',
                    xtype: 'checkbox',
                    listeners: {
                        check: function(checkbox,checked){
                            if (checked) {
                                that.form.findField('MorbusOnkoBasePS_IsTreatDelay').setValue(false);
                                that.form.findField('MorbusOnkoBasePS_IsNotTreat').setValue(false);
                            }
                        }
                    }
                }, {
                    boxLabel: lang['drugoe'],
                    hideLabel: true,
                    name: 'MorbusOnkoBasePS_IsOther',
                    anchor: '100%',
                    xtype: 'checkbox',
                    listeners: {
                        check: function(checkbox,checked){
                            if (checked) {
                                that.form.findField('MorbusOnkoBasePS_IsTreatDelay').setValue(false);
                                that.form.findField('MorbusOnkoBasePS_IsNotTreat').setValue(false);
                            }
                        }
                    }
                }]
            }, {
                fieldLabel: lang['sostoyanie_pri_vyipiske'],
                hiddenName: 'OnkoLeaveType_id',
                xtype: 'swcommonsprlikecombo',
                sortField:'OnkoLeaveType_Code',
                comboSubject: 'OnkoLeaveType',
                width: 400
			}],
            url:'/?c=MorbusOnkoBasePS&m=save',
			reader: new Ext.data.JsonReader({
				success: Ext.emptyFn
			}, [
				{name: 'MorbusOnkoBasePS_id'}, 
				{name: 'MorbusOnkoBase_id'}, 
				{name: 'MorbusOnkoBasePS_setDT'}, 
				{name: 'MorbusOnkoBasePS_disDT'}, 
				{name: 'OnkoHospType_id'},
                {name: 'OnkoPurposeHospType_id'},
                {name: 'Diag_id'},
                {name: 'Lpu_id'},
                {name: 'LpuSection_id'},
                {name: 'MorbusOnkoBasePS_IsTreatDelay'},
                {name: 'MorbusOnkoBasePS_IsNotTreat'},
                {name: 'MorbusOnkoBasePS_IsSurg'},
                {name: 'MorbusOnkoBasePS_IsBeam'},
                {name: 'MorbusOnkoBasePS_IsChem'},
                {name: 'MorbusOnkoBasePS_IsGormun'},
                {name: 'MorbusOnkoBasePS_IsImmun'},
                {name: 'MorbusOnkoBasePS_IsPreOper'},
                {name: 'MorbusOnkoBasePS_IsIntraOper'},
                {name: 'MorbusOnkoBasePS_IsPostOper'},
                {name: 'MorbusOnkoBasePS_IsOther'},
                {name: 'OnkoLeaveType_id'},
                {name: 'Evn_id'}
			])
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
			HelpButton(this),
			{
				handler: function() 
				{
					this.ownerCt.hide();
				},
				iconCls: 'cancel16',
				text: BTN_FRMCANCEL
			}],
			items:[this.InformationPanel, this.formPanel]
		});
		sw.Promed.swMorbusOnkoBasePSWindow.superclass.initComponent.apply(this, arguments);
		this.form = this.formPanel.getForm();
	}	
});