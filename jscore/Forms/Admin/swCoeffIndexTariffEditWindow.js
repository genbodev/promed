/**
 * swCoeffIndexTariffEditWindow - окно редактирования значений коэффициентов индексации
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Admin
 * @access			public
 * @copyright		Copyright (c) 2013 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			30.10.2013
 */

sw.Promed.swCoeffIndexTariffEditWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: true,
	autoScroll: true,
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	draggable: true,
	id: 'swCoeffIndexTariffEditWindow',
	maximizable: false,
	modal: true,
	resizable: false,
	width: 500,

	TariffClassArray: null,

	doSave: function() {
		var wnd = this;
		var base_form = this.FormPanel.getForm();

		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					wnd.FormPanel.getFirstInvalidEl().focus(false);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		var coeff_index_min = base_form.findField('CoeffIndex_id').getFieldValue('CoeffIndex_Min');
		var coeff_index_max = base_form.findField('CoeffIndex_id').getFieldValue('CoeffIndex_Max');
		var coeff_index_tariff_value = base_form.findField('CoeffIndexTariff_Value').getValue();

		if (coeff_index_tariff_value == 0 || coeff_index_tariff_value < 0) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				icon: Ext.Msg.WARNING,
				msg: lang['znachenie_doljno_byit_bolshe_nulya'],
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
		if (!Ext.isEmpty(coeff_index_min) && coeff_index_tariff_value < coeff_index_min) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				icon: Ext.Msg.WARNING,
				msg: lang['znachenie_doljno_byit_ne_menshe_minimalnogo_znacheniya_ki'],
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
		if (!Ext.isEmpty(coeff_index_max) && coeff_index_tariff_value > coeff_index_max) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				icon: Ext.Msg.WARNING,
				msg: lang['znachenie_doljno_byit_ne_bolshe_maksimalnogo_znacheniya_ki'],
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		var begdate = base_form.findField('CoeffIndexTariff_begDate').getValue();
		var enddate = base_form.findField('CoeffIndexTariff_endDate').getValue();
		if (!Ext.isEmpty(enddate) && enddate < begdate) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				icon: Ext.Msg.WARNING,
				msg: lang['data_okonchaniya_doljna_byit_pozje_datyi_nachala'],
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		wnd.getLoadMask().show();

		base_form.submit({
			failure: function(result_form, action) {
				wnd.getLoadMask().hide();
			},
			success: function(result_form, action) {
				wnd.getLoadMask().hide();

				if ( action.result ) {
					wnd.callback();
					wnd.hide();
				}
				else {
					sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki']);
				}
			}
		});
	},

	show: function() {
		sw.Promed.swCoeffIndexTariffEditWindow.superclass.show.apply(this, arguments);

		this.action = null;
		var form = this;
		var base_form = form.FormPanel.getForm();

		if ( arguments && arguments[0].action ) {
			this.action = arguments[0].action;
		}

		if ( arguments && arguments[0].callback ) {
			this.callback = arguments[0].callback;
		}

		if (arguments && arguments[0].TariffClassArray) {
			this.TariffClassArray = arguments[0].TariffClassArray;
		}

		base_form.reset();

		base_form.findField('TariffClass_id').getStore().clearFilter();
		if (form.TariffClassArray) {
			base_form.findField('TariffClass_id').getStore().filterBy(function(rec) {
				return (rec.get('TariffClass_id').inlist(form.TariffClassArray));
			});
		} else {
			base_form.findField('TariffClass_id').getStore().filterBy(function(rec) {return false});
		}

		if ( arguments[0].formParams ) {
			base_form.setValues(arguments[0].formParams);
		}

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT });
		loadMask.show();

		switch ( this.action ) {
			case 'add':
				this.enableEdit(true);
				this.setTitle(lang['znachenie_koeffitsienta_indeksatsii_dobavlenie']);

				base_form.clearInvalid();
				base_form.findField('TariffClass_id').focus(true, 250);

				if (base_form.findField('TariffClass_id').getStore().getCount() == 1) {
					var tariff_class_id = base_form.findField('TariffClass_id').getStore().getAt(0).get('TariffClass_id');
					base_form.findField('TariffClass_id').setValue(tariff_class_id);
				}

				setCurrentDateTime({
					dateField: base_form.findField('CoeffIndexTariff_begDate'),
					loadMask: false,
					setDate: true,
					setDateMaxValue: false,
					setDateMinValue: false,
					setTime: false,
					windowId: 'swCoeffIndexTariffEditWindow'
				});

				loadMask.hide();
				break;

			case 'edit':
			case 'view':
				var coeff_index_tariff_id = base_form.findField('CoeffIndexTariff_id').getValue();

				if ( !coeff_index_tariff_id ) {
					loadMask.hide();
					this.hide();
					return false;
				}

				var afterFormLoad = function() {
					loadMask.hide();
					if ( form.action == 'edit' ) {
						form.setTitle(lang['znachenie_koeffitsienta_indeksatsii_redaktirovanie']);
						form.enableEdit(true);
					}
					else {
						form.setTitle(lang['znachenie_koeffitsienta_indeksatsii_prosmotr']);
						form.enableEdit(false);
					}
					base_form.clearInvalid();
					if ( form.action == 'edit' ) {
						base_form.findField('TariffClass_id').focus(true, 250);
					}
					else {
						form.buttons[form.buttons.length - 1].focus();
					}
				};

				base_form.load({
					params: {CoeffIndexTariff_id: coeff_index_tariff_id},
					failure: function() {
						afterFormLoad();
					},
					success: function() {
						afterFormLoad();
					},
					url: '/?c=CoeffIndex&m=loadCoeffIndexTariffEditForm'
				});

				break;

			default:
				loadMask.hide();
				this.hide();
				break;
		}
	},

	initComponent: function() {
		var form = this;

		this.FormPanel = new Ext.form.FormPanel({
			bodyStyle: '{padding-top: 0.5em;}',
			border: false,
			frame: true,
			labelAlign: 'right',
			labelWidth: 160,
			layout: 'form',
			id: 'CoeffIndexTariffEditForm',
			url: '/?c=CoeffIndex&m=saveCoeffIndexTariff',
			autoLoad: false,
			reader: new Ext.data.JsonReader({
				success: Ext.emptyFn
			}, [
				{ name: 'CoeffIndexTariff_id' },
				{ name: 'TariffClass_id' },
				{ name: 'CoeffIndex_id' },
				{ name: 'CoeffIndexTariff_Value' },
				{ name: 'CoeffIndexTariff_begDate' },
				{ name: 'CoeffIndexTariff_endDate' }
			]),
			items: [{
				name: 'CoeffIndexTariff_id',
				xtype: 'hidden'
			}, {
				name: 'LpuSection_id',
				xtype: 'hidden'
			}, {
				lastQuery: '',
				allowBlank:false,
				hiddenName: 'TariffClass_id',
				fieldLabel: lang['vid_tarifa'],
				id: 'CITEW_TariffClass_id',
				xtype: 'swtariffclasscombo',
				width: 300,
				listWidth: 350,
				listeners:
				{
					'render': function(combo) {
						combo.getStore().load();
					}
				}
			}, {
				allowBlank: false,
				fieldLabel: lang['koeffitsient_indeksatsii'],
				hiddenName: 'CoeffIndex_id',
				xtype: 'swcoeffindexcombo',
				width: 300
			}, {
				allowBlank: false,
				allowDecimal: true,
				allowNegative: false,
				decimalPrecision: 4,
				fieldLabel: lang['znachenie'],
				name: 'CoeffIndexTariff_Value',
				xtype: 'numberfield',
				width: 300
			}, {
				xtype: 'fieldset',
				autoHeight: true,
				title: lang['period_deystviya'],
				items: [{
					border: false,
					layout: 'column',
					items: [{
						border: false,
						labelWidth: 70,
						layout: 'form',
						items: [{
							allowBlank: false,
							fieldLabel: lang['nachalo'],
							xtype: 'swdatefield',
							format: 'd.m.Y',
							plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
							name: 'CoeffIndexTariff_begDate'
						}]
					}, {
						border: false,
						labelWidth: 100,
						layout: 'form',
						items: [{
							fieldLabel : lang['okonchanie'],
							xtype: 'swdatefield',
							format: 'd.m.Y',
							plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
							name: 'CoeffIndexTariff_endDate'
						}]
					}]
				}]
			}]
		});

		Ext.apply(this, {
			items: [
				this.FormPanel
			],
			buttons: [{
				handler: function() {
					this.doSave();
				}.createDelegate(this),
				iconCls: 'save16',
				id: 'CITEW_SaveButton',
				text: BTN_FRMSAVE
			},
			'-',
			HelpButton(this, -1),
			{
				handler: function() {
					this.hide();
				}.createDelegate(this),
				iconCls: 'cancel16',
				id: 'CITEW_CancelButton',
				text: BTN_FRMCANCEL
			}]
		});

		sw.Promed.swCoeffIndexTariffEditWindow.superclass.initComponent.apply(this, arguments);
	}
});
