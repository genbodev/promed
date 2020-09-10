/**
 * swNumeratorRezervEditWindow - окно редактирования резервирования номеров
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Admin
 * @access			public
 * @copyright		Copyright (c) 2015 Swan Ltd.
 */

/*NO PARSE JSON*/

sw.Promed.swNumeratorRezervEditWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'swNumeratorRezervEditWindow',
	width: 500,
	autoHeight: true,
	modal: true,

	formStatus: 'edit',
	action: 'view',
	callback: Ext.emptyFn,

	doSave: function() {
		if (this.formStatus == 'save') {
			return false;
		}
		this.formStatus = 'save';

		var win = this;
		var base_form = this.FormPanel.getForm();

		if (!base_form.isValid()) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function()
				{
					this.formStatus = 'edit';
					this.FormPanel.getFirstInvalidEl().focus(true);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		if (!Ext.isEmpty(base_form.findField('NumeratorRezerv_To').getValue()) && base_form.findField('NumeratorRezerv_To').getValue() < base_form.findField('NumeratorRezerv_From').getValue()) {
			sw.swMsg.alert(lang['oshibka'], lang['znachenie_polya_nachalo_diapazona_bolshe_znacheniya_polya_konets_diapazona']);
			this.formStatus = 'edit';
			return false;
		}

		var params = {};
		if (base_form.findField('NumeratorRezerv_From').disabled) {
			params.NumeratorRezerv_From = base_form.findField('NumeratorRezerv_From').getValue();
		}

		if (this.formMode == 'remote') {
			win.getLoadMask(LOAD_WAIT_SAVE).show();

			base_form.submit({
				failure: function (result_form, action) {
					this.formStatus = 'edit';
					win.getLoadMask().hide();
				}.createDelegate(this),
				params: params,
				success: function (result_form, action) {
					win.getLoadMask().hide();
					if (action.result && action.result.busyNums) {
						sw.swMsg.alert(lang['vnimanie'], lang['nomera'] + action.result.busyNums + lang['uje_zavedenyi_v_sistemu_ukazannyie_nomera_ne_mogut_ispolzovatsya_v_kachestve_rezervnyih']);
					}
					if (typeof this.callback == 'function') {
						this.callback();
					}
					this.formStatus = 'edit';
					this.hide();
				}.createDelegate(this)
			});
		} else {
			if (typeof this.callback == 'function') {
				if (base_form.findField('Record_Status').getValue() == 1) {
					base_form.findField('Record_Status').setValue(2);
				}
				var data = [{
					'NumeratorRezerv_id': base_form.findField('NumeratorRezerv_id').getValue(),
					'NumeratorRezerv_From': base_form.findField('NumeratorRezerv_From').getValue(),
					'NumeratorRezerv_To': base_form.findField('NumeratorRezerv_To').getValue(),
					'Record_Status': base_form.findField('Record_Status').getValue()
				}];
				this.callback(data);
			}
			this.formStatus = 'edit';
			this.hide();
		}
	},
	show: function() {
		sw.Promed.swNumeratorRezervEditWindow.superclass.show.apply(this, arguments);

		var win = this;
		var base_form = win.FormPanel.getForm();

		base_form.reset();

		this.formMode = 'remote';
		if (arguments[0].formMode) {
			this.formMode = arguments[0].formMode;
		}

		if (arguments[0].action) {
			this.action = arguments[0].action;
		}

		if (arguments[0].callback) {
			this.callback = arguments[0].callback;
		}
		if (arguments[0].formParams) {
			base_form.setValues(arguments[0].formParams);
		}

		var winTitle = lang['diapazonyi_rezervirovaniya'];
		if (base_form.findField('NumeratorRezervType_id').getValue() == 2) {
			winTitle = lang['diapazonyi_generatsii'];
		}

		switch (this.action) {
			case 'add':
				win.enableEdit(true);
				win.setTitle(winTitle+lang['_dobavlenie']);
				break;

			case 'edit':
			case 'view':
				if (this.action == 'edit') {
					win.enableEdit(true);
					win.setTitle(winTitle+lang['_redaktirovanie']);
				} else {
					win.enableEdit(false);
					win.setTitle(winTitle+lang['_prosmotr']);
				}

				if (base_form.findField('NumeratorRezervType_id').getValue() == 2 && base_form.findField('NumeratorRezerv_id').getValue() > 0) {
					// При редактировании диапазона запрещать изменять значение поля «Начало диапазона».
					base_form.findField('NumeratorRezerv_From').disable();
				}

				if (this.formMode == 'remote') {
					win.getLoadMask(LOAD_WAIT).show();
					base_form.load({
						failure:function () {
							win.getLoadMask().hide();
							win.hide();
						},
						url: '/?c=Numerator&m=loadNumeratorRezervEditForm',
						params: {NumeratorRezerv_id: base_form.findField('NumeratorRezerv_id').getValue()},
						success: function() {
							win.getLoadMask().hide();
						}
					});
				}

				break;
		}

		if (base_form.findField('NumeratorRezerv_From').disabled) {
			win.buttons[0].focus();
		} else {
			base_form.findField('NumeratorRezerv_From').focus();
		}
	},

	initComponent: function() {
		var win = this;

		this.FormPanel = new Ext.form.FormPanel({
			bodyBorder: false,
			border: false,
			buttonAlign: 'left',
			frame: true,
			url: '/?c=Numerator&m=saveNumeratorRezerv',
			labelWidth: 160,
			labelAlign: 'right',

			items: [{
				name: 'NumeratorRezerv_id',
				xtype: 'hidden'
			}, {
				name: 'Numerator_id',
				xtype: 'hidden'
			}, {
				name: 'NumeratorRezervType_id',
				xtype: 'hidden'
			}, {
				name: 'Record_Status',
				xtype: 'hidden'
			}, {
				allowBlank: false,
				name: 'NumeratorRezerv_From',
				minValue: 1,
				maxValue: 999999999,
				maxLength: 9,
				autoCreate: {tag: "input", maxLength: "9", autocomplete: "off"},
				allowDecimals: false,
				allowNegative: false,
				fieldLabel: lang['nachalo_diapazona'],
				xtype: 'numberfield',
				anchor: '-10'
			}, {
				allowBlank: true,
				name: 'NumeratorRezerv_To',
				minValue: 1,
				maxValue: 999999999,
				maxLength: 9,
				autoCreate: {tag: "input", maxLength: "9", autocomplete: "off"},
				allowDecimals: false,
				allowNegative: false,
				fieldLabel: lang['konets_diapazona'],
				xtype: 'numberfield',
				anchor: '-10'
			}],
			reader: new Ext.data.JsonReader({
				success: function() { }
			}, [
				{name: 'NumeratorRezerv_id'},
				{name: 'Numerator_id'},
				{name: 'NumeratorRezerv_From'},
				{name: 'NumeratorRezerv_To'},
				{name: 'NumeratorRezervType_id'}
			]),
			keys: [{
				fn: function(e) {
					this.doSave();
				}.createDelegate(this),
				key: Ext.EventObject.ENTER,
				stopEvent: true
			}]
		});

		Ext.apply(this, {
			items: [
				this.FormPanel
			],
			buttons: [
				{
					text: BTN_FRMSAVE,
					tooltip: lang['sohranit'],
					iconCls: 'save16',
					handler: function()
					{
						this.doSave();
					}.createDelegate(this)
				}, {
					text: '-'
				},
				HelpButton(this, 1),
				{
					handler: function () {
						this.hide();
					}.createDelegate(this),
					iconCls: 'cancel16',
					text: lang['otmenit']
				}]
		});

		sw.Promed.swNumeratorRezervEditWindow.superclass.initComponent.apply(this, arguments);
	}
});