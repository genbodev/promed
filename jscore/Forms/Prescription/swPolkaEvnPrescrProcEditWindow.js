/**
* swPolkaEvnPrescrProcEditWindow - окно добавления курса назначений c типом Манипуляции и процедуры PrescriptionType_id = 6
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Prescription
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @version      0.001-03.04.2012
* @comment      Префикс для id компонентов EPRPREF (PolkaEvnPrescrProcEditForm)
*				tabIndex: TABINDEX_EVNPRESCR + (от 130 до 159)
*/
/*NO PARSE JSON*/

sw.Promed.swPolkaEvnPrescrProcEditWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	objectName: 'swPolkaEvnPrescrProcEditWindow',
	objectSrc: '/jscore/Forms/Prescription/swPolkaEvnPrescrProcEditWindow.js',
	winForm:null,
	action: null,
	autoHeight: true,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	collapsible: false,
	doSave: function(options) {

		var thas = this;
		if ( this.formStatus == 'save' ) {
			return false;
		}

		if ( typeof options != 'object' ) {
			options = new Object();
		}

		var base_form = this.FormPanel.getForm();

		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
                    thas.formStatus = 'edit';
                    thas.FormPanel.getFirstInvalidEl().focus(true);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

        base_form.findField('UslugaComplex_id').setValue(this.UslugaComplexPanel.getValues().toString());

        var params = base_form.getValues();
		params.parentEvnClass_SysNick = this.parentEvnClass_SysNick;
		if(options.signature) {
			params.signature = 1;
		} else {
			params.signature = 0;
		}
		
		if( base_form.findField('DurationType_id').disabled ) {
			params.DurationType_id = base_form.findField('DurationType_id').getValue();
		}

		if( base_form.findField('DurationType_recid').disabled ) {
			params.DurationType_recid = base_form.findField('DurationType_recid').getValue();
		}

		if( base_form.findField('DurationType_intid').disabled ) {
			params.DurationType_intid = base_form.findField('DurationType_intid').getValue();
		}

        if (this.mode == 'nosave') {
            var data = params;
			data.Usluga_List = this.UslugaComplexPanel.getValues().toString();
            data.DurationTypeP_Nick = base_form.findField('DurationType_id').getRawValue();
            data.DurationTypeN_Nick = base_form.findField('DurationType_recid').getRawValue();
            data.DurationTypeI_Nick = base_form.findField('DurationType_intid').getRawValue();
			data.CourseDuration = base_form.findField('EvnCourseProc_Duration').getValue();
            this.callback({EvnPrescrProcData: data});
            this.hide();
            return true;
        }
        var loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT_SAVE });
        loadMask.show();
		this.formStatus = 'save';

        Ext.Ajax.request({
            params: params,
            callback: function(options, success, response) {
                thas.formStatus = 'edit';
                loadMask.hide();
                if ( success ) {
                    var response_obj = Ext.util.JSON.decode(response.responseText);
                    if (response_obj.success) {

                        var data = base_form.getValues();
                        if (thas.winForm=='uslugaInput') {
                            data.Usluga_List = thas.UslugaComplexPanel.getUslugaComboTextValues(0);
                            data.DurationType_id = base_form.findField('DurationType_id').getValue();
                            data.DurationTypeP_Nick = base_form.findField('DurationType_id').getRawValue();
                            data.DurationType_recid = base_form.findField('DurationType_recid').getValue();
                            data.DurationTypeN_Nick = base_form.findField('DurationType_recid').getRawValue();
                            data.DurationTypeI_Nick = base_form.findField('DurationType_intid').getRawValue();
                        } else {
                            data.EvnCourseProc_id = response_obj.EvnCourseProc_id;
                            data.EvnPrescrProc_id = response_obj['EvnPrescrProc_id0'];
                        }
                        thas.callback({EvnPrescrProcData: data});
                        thas.hide();
                    } else {
                        sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_2]']);
                    }
                } else {
                    sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_1]']);
                }
            },
            url: '/?c=EvnPrescr&m=saveEvnCourseProc'
        });
		return true;
		
	},
	draggable: true,
	setFieldsDisabled: function(d) 
	{
		this.FormPanel.items.each(function(f) 
		{
			if (f && (f.xtype!='hidden') && (f.xtype!='fieldset')  && (f.changeDisabled!==false))
			{
				f.setDisabled(d);
			}
		});
		if ( d == false ) {
			this.buttons[0].show();
			//Заведение графика сделать возможным только в днях
			if(this.parentEvnClass_SysNick == 'EvnSection') {
				var base_form = this.FormPanel.getForm();
				base_form.findField('DurationType_id').setValue(1);
				base_form.findField('DurationType_recid').setValue(1);
				base_form.findField('DurationType_intid').setValue(1);
				base_form.findField('DurationType_id').disable();
				base_form.findField('DurationType_recid').disable();
				base_form.findField('DurationType_intid').disable();
			}
		}
		else {
			this.buttons[0].hide();
		}
	},
	formStatus: 'edit',
	id: 'PolkaEvnPrescrProcEditWindow',
	initComponent: function() {
		this.UslugaComplexPanel = new sw.Promed.UslugaComplexPanel({
			win: this,
			firstTabIndex: TABINDEX_EVNPRESCR + 131,
			PrescriptionType_Code: 6,
			disabledAddUslugaComplex: true,
			labelWidth: 155,
			bodyStyle: 'padding: 0'
		});

		this.FormPanel = new Ext.form.FormPanel({
			autoHeight: true,
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			border: false,
			frame: false,
			id: 'PolkaEvnPrescrProcEditForm',
			labelAlign: 'right',
			labelWidth: 155,
			reader: new Ext.data.JsonReader({
			},  [
				{ name: 'EvnCourseProc_setDate' },
				{ name: 'EvnCourseProc_MaxCountDay' },
				{ name: 'EvnCourseProc_Duration' },
				{ name: 'EvnCourseProc_ContReception' },
				{ name: 'EvnCourseProc_Interval' },
				{ name: 'DurationType_id' },
				{ name: 'DurationType_recid' },
				{ name: 'DurationType_intid' },
				{ name: 'EvnPrescrProc_IsCito' },
				{ name: 'EvnPrescrProc_Descr' },
				{ name: 'accessType' },
				{ name: 'EvnCourseProc_id' },
				{ name: 'EvnCourseProc_pid' },
				{ name: 'UslugaComplex_id' },
                { name: 'MedPersonal_id' },
                { name: 'LpuSection_id' },
                { name: 'Morbus_id' },
                { name: 'EvnCourseProc_MinCountDay' },
                { name: 'ResultDesease_id' },
                { name: 'PersonEvn_id' },
				{ name: 'Server_id' }
			]),
			region: 'center',
			url: '/?c=EvnPrescr&m=saveEvnCourseProc',

			items: [{
				name: 'accessType', // Режим доступа
				value: '',
				xtype: 'hidden'
			}, {
				name: 'EvnCourseProc_id', // Идентификатор курса
				value: null,
				xtype: 'hidden'
			}, {
                name: 'EvnCourseProc_pid', // Идентификатор события
                value: null,
                xtype: 'hidden'
            }, {
				name: 'PersonEvn_id', // Идентификатор состояния человека
				value: null,
				xtype: 'hidden'
			}, {
                name: 'Server_id', // Идентификатор сервера
                value: null,
                xtype: 'hidden'
            }, {
                name: 'MedPersonal_id', // Врач, создавший курс
                value: null,
                xtype: 'hidden'
            }, {
                name: 'LpuSection_id', // Отделение врача, создавшего курс
                value: null,
                xtype: 'hidden'
            }, {
                name: 'Morbus_id',
                value: null,
                xtype: 'hidden'
            }, {
                name: 'EvnCourseProc_MinCountDay',
                value: null,
                xtype: 'hidden'
            }, {
                name: 'ResultDesease_id',
                value: null,
                xtype: 'hidden'
            }, {
				name: 'UslugaComplex_id',
				value: null,
				xtype: 'hidden'
			}, {
				fieldLabel: lang['data_nachala'],
				format: 'd.m.Y',
				name: 'EvnCourseProc_setDate',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				selectOnFocus: true,
				width: 100,
				tabIndex: TABINDEX_EVNPRESCR + 130,
				onChange: function(field, newValue, oldValue) {
					var date_str = field.getRawValue() || null;
					this.UslugaComplexPanel.setUslugaComplexDate(date_str);
				}.createDelegate(this),
				xtype: 'swdatefield'
			},
			this.UslugaComplexPanel,
			{
				allowDecimals: false,
				allowNegative: false,
				fieldLabel: lang['povtorov_v_sutki'],
				value: 1,
				minValue: 1,
				style: 'text-align: right;', 
				name: 'EvnCourseProc_MaxCountDay',
				width: 100,
				tabIndex: TABINDEX_EVNPRESCR + 144,
				xtype: 'numberfield'
			}, {
				border: false,
				layout: 'column',
				items: [{
					border: false,
					labelWidth: 155,
					layout: 'form',
					items: [{
						allowDecimals: false,
						allowNegative: false,
						fieldLabel: lang['prodoljitelnost'],
						value: 1,
						minValue: 1,
						style: 'text-align: right;', 
						name: 'EvnCourseProc_Duration',
						width: 100,
						tabIndex: TABINDEX_EVNPRESCR + 145,
						listeners: {
							'change': function(field, newValue, oldValue) {
								this.FormPanel.getForm().findField('EvnCourseProc_ContReception').setValue(newValue);
								return true;
							}.createDelegate(this)
						},
						xtype: 'numberfield'
					},{
						allowDecimals: false,
						allowNegative: false,
						fieldLabel: lang['povtoryat_nepreryivno'],
						value: 1,
						minValue: 1,
						style: 'text-align: right;', 
						name: 'EvnCourseProc_ContReception',
						width: 100,
						tabIndex: TABINDEX_EVNPRESCR + 147,
						xtype: 'numberfield'
					},{
						allowDecimals: false,
						allowNegative: false,
						fieldLabel: lang['pereryiv'],
						value: 0,
						minValue: 0,
						style: 'text-align: right;', 
						name: 'EvnCourseProc_Interval',
						width: 100,
						tabIndex: TABINDEX_EVNPRESCR + 149,
						xtype: 'numberfield'
					}]
				},{
					border: false,
					layout: 'form',
					style: 'margin-left: 10px; padding: 0px;',
					items: [{
						hiddenName: 'DurationType_id',//Тип продолжительности
						width: 70,
						value: 1,
						tabIndex: TABINDEX_EVNPRESCR + 146,
						listeners: {
							'change': function(combo, newValue, oldValue) {
								var base_form = this.FormPanel.getForm();
								var record = combo.getStore().getById(newValue);
								if ( !record ) {
									return false;
								}
								base_form.findField('DurationType_recid').setValue(newValue);
								base_form.findField('DurationType_intid').setValue(newValue);
								return true;
							}.createDelegate(this)
						},
						xtype: 'swdurationtypecombo'
					},{
						hiddenName: 'DurationType_recid',//Тип Непрерывный прием
						width: 70,
						value: 1,
						tabIndex: TABINDEX_EVNPRESCR + 148,
						xtype: 'swdurationtypecombo'
					},{
						hiddenName: 'DurationType_intid',//Тип Перерыв
						width: 70,
						value: 1,
						tabIndex: TABINDEX_EVNPRESCR + 150,
						xtype: 'swdurationtypecombo'
					}]
				}]
			}, {
				boxLabel: 'Cito',
				checked: false,
				fieldLabel: '',
				labelSeparator: '',
				name: 'EvnPrescrProc_IsCito',
				tabIndex: TABINDEX_EVNPRESCR + 151,
				xtype: 'checkbox'
			}, {
				fieldLabel: lang['kommentariy'],
				height: 70,
				name: 'EvnPrescrProc_Descr',
				anchor: '99%',
				tabIndex: TABINDEX_EVNPRESCR + 152,
				xtype: 'textarea'
			}]
		});

		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.doSave();
				}.createDelegate(this),
				iconCls: 'save16',
				tabIndex: TABINDEX_EVNPRESCR + 155,
				text: BTN_FRMSAVE
			}, {
                hidden: true,
				handler: function() {
					this.doSave({signature: true});
				}.createDelegate(this),
				iconCls: 'signature16',
				tabIndex: TABINDEX_EVNPRESCR + 156,
				text: BTN_FRMSIGN
			}, {
				text: '-'
			},
			//HelpButton(this, -1),
			{
				handler: function() {
					this.hide();
				}.createDelegate(this),
				iconCls: 'cancel16',
				onTabAction: function () {
					this.FormPanel.getForm().findField('EvnCourseProc_setDate').focus(true, 250);
				}.createDelegate(this),
				tabIndex: TABINDEX_EVNPRESCR + 159,
				text: BTN_FRMCANCEL
			}],
			items: [
				this.FormPanel
			],
			layout: 'form'
		});

		sw.Promed.swPolkaEvnPrescrProcEditWindow.superclass.initComponent.apply(this, arguments);
	},
	keys: [{
		alt: true,
		fn: function(inp, e) {
			switch ( e.getKey() ) {
				case Ext.EventObject.C:
					this.doSave();
				break;

				case Ext.EventObject.J:
					this.hide();
				break;
			}
		}.createDelegate(this),
		key: [
			Ext.EventObject.C,
			Ext.EventObject.J
		],
		scope: this,
		stopEvent: false
	}],
	layout: 'form',
	listeners: {
		'beforehide': function(win) {
			//
			//win.UslugaComplexPanel.disable();
		},
		'hide': function(win) {
			win.onHide();
		}
	},
	loadMask: null,
	maximizable: false,
	maximized: false,
	modal: true,
	onHide: Ext.emptyFn,
	plain: true,
	resizable: false,
	show: function() {
		sw.Promed.swPolkaEvnPrescrProcEditWindow.superclass.show.apply(this, arguments);

		this.center();

		var base_form = this.FormPanel.getForm();
		base_form.reset();

		this.parentEvnClass_SysNick = null;
        this.action = null;
        this.mode = 'save';
		this.winForm = null;
		this.callback = Ext.emptyFn;
		this.formStatus = 'edit';
		this.onHide = Ext.emptyFn;
		
		if ( !arguments[0] || !arguments[0].formParams ) {
			sw.swMsg.alert(lang['soobschenie'], lang['nevernyie_parametryi'], function() { this.hide(); }.createDelegate(this) );
			return false;
		}

		base_form.setValues(arguments[0].formParams);

        if ( arguments[0].action && typeof arguments[0].action == 'string' ) {
            this.action = arguments[0].action;
        }

        if ( arguments[0].mode && typeof arguments[0].mode == 'string' ) {
            this.mode = arguments[0].mode;
        }
        if ( arguments[0].parentEvnClass_SysNick && typeof arguments[0].parentEvnClass_SysNick == 'string' ) {
			this.parentEvnClass_SysNick = arguments[0].parentEvnClass_SysNick;
		}

		if ( arguments[0].callback && typeof arguments[0].callback == 'function' ) {
			this.callback = arguments[0].callback;
		}

		if ( arguments[0].onHide && typeof arguments[0].onHide == 'function' ) {
			this.onHide = arguments[0].onHide;
		}
		if ( arguments[0].winForm && typeof arguments[0].winForm == 'string' ) {
            this.winForm = arguments[0].winForm;
        }
		if ( arguments[0].formParams.mode && typeof arguments[0].formParams.mode == 'string' ) {
            this.mode = arguments[0].formParams.mode;
        }
		this.UslugaComplexPanel.UslugaComplex_Date = null;
		this.getLoadMask(LOAD_WAIT).show();

		switch ( this.action ) {
			case 'add':
				this.getLoadMask().hide();
				base_form.clearInvalid();
				this.setTitle(lang['naznachenie_manipulyatsiy_i_protsedur_dobavlenie']);
				this.setFieldsDisabled(false);

                if (base_form.findField('EvnCourseProc_setDate').getValue()) {
                    this.UslugaComplexPanel.UslugaComplex_Date = base_form.findField('EvnCourseProc_setDate').getRawValue();
                }

				this.UslugaComplexPanel.setValues([base_form.findField('UslugaComplex_id').getValue()]);

                if (base_form.findField('UslugaComplex_id').getValue()) {
                    this.UslugaComplexPanel.setDisabled(true);
                }

				base_form.findField('EvnCourseProc_setDate').focus(true, 250);
			break;

			case 'addwithgrafcopy':
			case 'edit':
			case 'view':
				if(this.mode=='nosave'){
					this.getLoadMask().hide();
					base_form.clearInvalid();
					this.setTitle(lang['naznachenie_manipulyatsiy_i_protsedur_redaktirovanie']);
					this.setFieldsDisabled(false);

					this.UslugaComplexPanel.UslugaComplex_Date = base_form.findField('EvnCourseProc_setDate').getRawValue();
					base_form.findField('EvnCourseProc_setDate').focus(true, 250);
					this.UslugaComplexPanel.setValues([base_form.findField('UslugaComplex_id').getValue()]);
					
				}
				else{
					base_form.load({
						failure: function() {
							this.getLoadMask().hide();
							sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_zagruzke_dannyih_formyi'], function() { this.hide(); }.createDelegate(this) );
						}.createDelegate(this),
						params: {
							'EvnCourseProc_id': base_form.findField('EvnCourseProc_id').getValue(),
							'parentEvnClass_SysNick': this.parentEvnClass_SysNick
						},
						success: function(frm, act) {
							this.getLoadMask().hide();
							base_form.clearInvalid();
							if ( this.action == 'addwithgrafcopy' ) {
								base_form.findField('accessType').setValue(null);
                                base_form.findField('EvnCourseProc_id').setValue(null);
								base_form.findField('EvnPrescrProc_IsCito').setValue('off');
								base_form.findField('EvnPrescrProc_Descr').setValue('');
								base_form.findField('UslugaComplex_id').setValue(null);
								/*
								copy params:
								{ name: 'EvnCourseProc_setDate' },
								{ name: 'EvnCourseProc_MaxCountDay' },
								{ name: 'EvnCourseProc_Duration' },
								{ name: 'EvnCourseProc_ContReception' },
								{ name: 'EvnCourseProc_Interval' },
								{ name: 'DurationType_id' },
								{ name: 'DurationType_recid' },
								{ name: 'DurationType_intid' },
								{ name: 'PersonEvn_id' },
								{ name: 'Server_id' }
								*/
								this.setTitle(lang['naznachenie_manipulyatsiy_i_protsedur_dobavlenie']);
								this.setFieldsDisabled(false);
								this.UslugaComplexPanel.setValues([null]);
								base_form.findField('EvnCourseProc_setDate').focus(true, 250);
								return true;
							}

							if ( base_form.findField('accessType').getValue() == 'view' ) {
								this.action = 'view';
							}

							if ( this.action == 'edit' ) {
								this.setTitle(lang['naznachenie_manipulyatsiy_i_protsedur_redaktirovanie']);
								this.setFieldsDisabled(false);
								this.UslugaComplexPanel.UslugaComplex_Date = base_form.findField('EvnCourseProc_setDate').getRawValue();
								
								base_form.findField('EvnCourseProc_setDate').focus(true, 250);
							}
							else {
								this.setTitle(lang['naznachenie_manipulyatsiy_i_protsedur_prosmotr']);
								this.setFieldsDisabled(true);
							}
							this.UslugaComplexPanel.setValues([base_form.findField('UslugaComplex_id').getValue()]);
							if(this.winForm=='uslugaInput'){
								this.UslugaComplexPanel.disable();
							}else{
								this.UslugaComplexPanel.enable();
							}
						}.createDelegate(this),
						url: '/?c=EvnPrescr&m=loadEvnCourseProcEditForm'
					});
				}
			break;

			default:
				this.getLoadMask().hide();
				this.hide();
			break;
		}

	},
	width: 550
});