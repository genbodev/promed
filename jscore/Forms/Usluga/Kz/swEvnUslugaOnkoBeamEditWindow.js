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
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	draggable: true,
	formMode: 'remote',
	formStatus: 'edit',
	layout: 'border',
	modal: true,
	width: 800,
	height: 480,
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
					thas.findById('EvnUslugaOnkoBeamEditForm').getFirstInvalidEl().focus(true);
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
					Morbus_id: morbus_id
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
		this.form.reset();
		
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

				thas.form.findField('EvnUslugaOnkoBeam_setTime').setValue('00:00');
				thas.form.findField('EvnUslugaOnkoBeam_disTime').setValue('00:00');

				thas.InformationPanel.load({
					Person_id: arguments[0].formParams.Person_id
				});
				loadMask.hide();
				thas.setAllowedDates();
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
					xtype: 'hidden'
				}, {
					name: 'EvnUslugaOnkoBeam_pid',
					xtype: 'hidden'
				}, {
					name: 'EvnUslugaOnkoBeam_setTime',
					xtype: 'hidden'
				}, {
					name: 'EvnUslugaOnkoBeam_disTime',
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
					fieldLabel: 'Басталған күн (Дата начала)',//lang['data_nachala'],
					name: 'EvnUslugaOnkoBeam_setDate',
					listeners: {
						'change': function(field, newValue) {
							thas.setAllowedDatesForDisField();
						}
					},
                    allowBlank: false,
					xtype: 'swdatefield',
					plugins: [new Ext.ux.InputTextMask('99.99.9999', false)]
				}, {
					fieldLabel: 'Аяқталған күн (Дата окончания)',//lang['data_okonchaniya'],
					name: 'EvnUslugaOnkoBeam_disDate',
					xtype: 'swdatefield',
					plugins: [new Ext.ux.InputTextMask('99.99.9999', false)]
				}, {
					fieldLabel: 'Сәулелеу тәсiлi (Способ облучения)',//lang['sposob_oblucheniya'],
					hiddenName: 'OnkoUslugaBeamIrradiationType_id',
					xtype: 'swcommonsprlikecombo',
					allowBlank: false,
					sortField:'OnkoUslugaBeamIrradiationType_Code',
					comboSubject: 'OnkoUslugaBeamIrradiationType',
					width: 400
				}, {
					fieldLabel: 'Сәулемен емдеу түрi (Вид лучевого лечения)',//lang['vid_luchevoy_terapii'],
					hiddenName: 'OnkoUslugaBeamKindType_id',
					xtype: 'swcommonsprlikecombo',
					allowBlank: false,
					sortField:'OnkoUslugaBeamKindType_Code',
					comboSubject: 'OnkoUslugaBeamKindType',
					width: 400
				}, {
					fieldLabel: 'Сәулелік ем әдісі (Методы лучевой терапии)',//lang['metod_luchevoy_terapii'],
					hiddenName: 'OnkoUslugaBeamMethodType_id',
					xtype: 'swcommonsprlikecombo',
					allowBlank: false,
					sortField:'OnkoUslugaBeamMethodType_Code',
					comboSubject: 'OnkoUslugaBeamMethodType',
					width: 400
				}, {
					fieldLabel: 'Радиомодификаторларды қолдану (Использование радиомодификаторов)',//lang['radiomodifikatoryi'],
					hiddenName: 'OnkoUslugaBeamRadioModifType_id',
					xtype: 'swcommonsprlikecombo',
					allowBlank: true,
					sortField:'OnkoUslugaBeamRadioModifType_Code',
					comboSubject: 'OnkoUslugaBeamRadioModifType',
					width: 400
				},  
                {
					xtype: 'fieldset',
					layout: 'column',
					height:120,
					labelWidth: 220,
					title: 'Сәулелеудiң ошақтық қосынды дозасы (Суммарная очаговая доза облучения (Гр))',
					border: false,
					bodyStyle:'background:#DFE8F6;padding:5px;',
					items: [{
                        layout: 'form',
                        border: false,
                        labelWidth: 380,
                        width: 470,
                        bodyStyle:'background:#DFE8F6;',
                        items: [{
                            fieldLabel: 'iсiкке (на опухоль)',//lang['summarnaya_doza_oblucheniya_opuholi'],
                            name: 'EvnUslugaOnkoBeam_TotalDoseTumor',
                            xtype: 'numberfield',
                            tabIndex: TABINDEX_EUCOMEF + 14,
                            autoCreate: {tag: "input", maxLength: "8", autocomplete: "off"},
                            width: 80
                        }, {
                            fieldLabel: 'метастаздарға (на метастазы)',//lang['summarnaya_doza_oblucheniya_zon_regionarnogo_metastazirovaniya'],
                            name: 'EvnUslugaOnkoBeam_TotalDoseRegZone',
                            xtype: 'numberfield',
                            tabIndex: TABINDEX_EUCOMEF + 16,
                            autoCreate: {tag: "input", maxLength: "8", autocomplete: "off"},
                            width: 80
                        }, {
                            fieldLabel: 'аймағындағы лимфа түйiндерiне (на регионарные лимфоузлы)',
                            name: 'EvnUslugaOnkoBeam_TotalDoseLymph',
                            xtype: 'numberfield',
                            tabIndex: TABINDEX_EUCOMEF + 16,
                            autoCreate: {tag: "input", maxLength: "8", autocomplete: "off"},
                            width: 80
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
                {name: 'OnkoTreatType_id'},
                {name: 'TreatmentConditionsType_id'},
                {name: 'EvnUslugaOnkoBeam_TotalDoseTumor'},
                {name: 'EvnUslugaOnkoBeam_TotalDoseLymph'},
                {name: 'EvnUslugaOnkoBeam_TotalDoseRegZone'}
			]),
			url: '/?c=EvnUslugaOnkoBeam&m=save'
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
		sw.Promed.swEvnUslugaOnkoBeamEditWindow.superclass.initComponent.apply(this, arguments);
		this.form = this.findById('EvnUslugaOnkoBeamEditForm').getForm();
	}	
});
