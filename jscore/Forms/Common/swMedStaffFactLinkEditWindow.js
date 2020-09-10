/**
 * swMedStaffFactLinkEditWindow - окно редактирования связки места работы врача и среднего мед. персонала
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Common
 * @access			public
 * @copyright		Copyright (c) 2013 Swan Ltd.
 * @author			Bykov Stanislav (savage@swan.perm.ru)
 * @version			08.10.2013
 */

sw.Promed.swMedStaffFactLinkEditWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: true,
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	doSave: function() {
		if ( this.action == 'view' || this.formStatus == 'save' ) {
			return false;
		}

		var base_form = this.FormPanel.getForm();
		var wnd = this;

		wnd.formStatus = 'save';

		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					wnd.formStatus = 'edit';
					wnd.FormPanel.getFirstInvalidEl().focus(false);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		var params = new Object();

		if ( base_form.findField('MedStaffFact_sid').disabled ) {
			params.MedStaffFact_sid = base_form.findField('MedStaffFact_sid').getValue();
		}

		if ( base_form.findField('MedStaffFactLink_begDT').disabled ) {
			params.MedStaffFactLink_begDT = Ext.util.Format.date(base_form.findField('MedStaffFactLink_begDT').getValue(), 'd.m.Y');
		}

		wnd.getLoadMask("Подождите, идет сохранение...").show();

		base_form.submit({
			failure: function(result_form, action) {
				wnd.formStatus = 'edit';
				sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_1]']);
				wnd.getLoadMask().hide();
			},
			params: params,
			success: function(result_form, action) {
				wnd.formStatus = 'edit';
				wnd.getLoadMask().hide();

				if ( action.result ) {
					// Собираем данные для отдачи в callback

					wnd.callback();
					wnd.hide();
				}
				else {
					sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_2]']);
				}
			}
		});
	},
	draggable: true,
	id: 'swMedStaffFactLinkEditWindow',
	initComponent: function() {
		var form = this;

		this.FormPanel = new Ext.form.FormPanel({
			autoScroll: true,
			bodyBorder: false,
			bodyStyle: 'padding: 0px;',
			border: false,
			frame: true,
			id: 'MedStaffFactLinkEditForm',
			labelAlign: 'right',
			labelWidth: 170,
			region: 'center',
			style: 'margin-bottom: 0.5em;',
			url: '/?c=MedStaffFactLink&m=saveMedStaffFactLink',

			reader: new Ext.data.JsonReader({
				success: Ext.emptyFn
			},  [
				{ name: 'MedStaffFactLink_id' },
				{ name: 'MedStaffFact_id' },
				{ name: 'MedStaffFact_sid' },
				{ name: 'MedStaffFactLink_begDT' },
				{ name: 'MedStaffFactLink_endDT' }
			]),

			items: [{
				name: 'MedStaffFactLink_id',
				value: -1,
				xtype: 'hidden'
			}, {
				name: 'MedStaffFact_id',
				value: -1,
				xtype: 'hidden'
			}, {
				allowBlank: false,
				hiddenName: 'MedStaffFact_sid',
				id: 'MSFLEF_MedStaffFactCombo',
				fieldLabel: lang['sotrudnik'],
				lastQuery: '',
				listWidth: 600,
				width: 400,
				xtype: 'swmedstafffactglobalcombo'
			}, {
				border: false,
				layout: 'column',
				items: [{
					border: false,
					layout: 'form',
					items: [{
						allowBlank: false,
						fieldLabel: lang['data_nachala'],
						format: 'd.m.Y',
						name: 'MedStaffFactLink_begDT',
						plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
						width: 100,
						xtype: 'swdatefield'
					}]
				}, {
					border: false,
					labelWidth: 120,
					layout: 'form',
					items: [{
						allowBlank: true,
						fieldLabel: lang['data_okonchaniya'],
						format: 'd.m.Y',
						name: 'MedStaffFactLink_endDT',
						plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
						width: 100,
						xtype: 'swdatefield'
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
				id: 'FCEW_SaveButton',
				text: BTN_FRMSAVE
			},
			'-',
			HelpButton(this, -1),
			{
				handler: function() {
					this.hide();
				}.createDelegate(this),
				iconCls: 'cancel16',
				id: 'FCEW_CancelButton',
				text: BTN_FRMCANCEL
			}]
		});

		sw.Promed.swMedStaffFactLinkEditWindow.superclass.initComponent.apply(this, arguments);

		setMedStaffFactGlobalStoreFilter({
			onDate: Ext.util.Format.date(new Date(), 'd.m.Y'),
			isMidMedPersonal: true
		});

		form.findById('MSFLEF_MedStaffFactCombo').getStore().removeAll();
		form.findById('MSFLEF_MedStaffFactCombo').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));

		form.findById('MSFLEF_MedStaffFactCombo').addListener('change', function(combo, newValue, oldValue) {
			var index = combo.getStore().findBy(function(rec) {
				return (rec.get('MedStaffFact_id') == newValue);
			});

			combo.fireEvent('select', combo, combo.getStore().getAt(index));
		}.createDelegate(this));

		form.findById('MSFLEF_MedStaffFactCombo').addListener('select', function(combo, record, id) {
			var base_form = this.FormPanel.getForm();

			base_form.findField('MedStaffFactLink_begDT').disable();
			base_form.findField('MedStaffFactLink_endDT').disable();

			base_form.findField('MedStaffFactLink_begDT').maxValue = undefined;
			base_form.findField('MedStaffFactLink_endDT').maxValue = undefined;
			base_form.findField('MedStaffFactLink_begDT').minValue = undefined;
			base_form.findField('MedStaffFactLink_endDT').minValue = undefined;

			if ( !Ext.isEmpty(record) && typeof record == 'object' && !Ext.isEmpty(record.get('MedStaffFact_id')) ) {
				if ( form.action == 'add' ) {
					base_form.findField('MedStaffFactLink_begDT').enable();
					base_form.findField('MedStaffFactLink_endDT').enable();
				}
				else if ( form.action == 'edit' ) {
					base_form.findField('MedStaffFactLink_endDT').enable();
				}

				var
					cur_date = new Date(),
					mp_beg_date = Date.parseDate(record.get('WorkData_begDate'), 'd.m.Y'),
					mp_end_date = Date.parseDate(record.get('WorkData_endDate'), 'd.m.Y');

				if ( !Ext.isEmpty(mp_beg_date) ) {
					base_form.findField('MedStaffFactLink_begDT').minValue = mp_beg_date;
					base_form.findField('MedStaffFactLink_endDT').minValue = (mp_beg_date > cur_date ? mp_beg_date : cur_date);
				}

				if ( !Ext.isEmpty(mp_end_date) ) {
					base_form.findField('MedStaffFactLink_begDT').maxValue = mp_end_date;
					base_form.findField('MedStaffFactLink_endDT').maxValue = mp_end_date;
				}
			}
		}.createDelegate(this));
	},
	maximizable: false,
	modal: true,
	resizable: false,
	show: function() {
		sw.Promed.swMedStaffFactLinkEditWindow.superclass.show.apply(this, arguments);

		this.center();

		var base_form = this.FormPanel.getForm();
		base_form.reset();

		this.action = null;
		this.callback = Ext.emptyFn;
		this.formStatus = 'edit';
		this.onHide = Ext.emptyFn;

		if ( !arguments[0] || !arguments[0].formParams ) {
			sw.swMsg.alert(lang['soobschenie'], lang['nevernyie_parametryi'], function() { this.hide(); }.createDelegate(this) );
			return false;
		}

		base_form.setValues(arguments[0].formParams);

		if ( arguments[0].action ) {
			this.action = arguments[0].action;
		}

		if ( arguments[0].callback ) {
			this.callback = arguments[0].callback;
		}

		if ( arguments[0].onHide ) {
			this.onHide = arguments[0].onHide;
		}

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT });
		loadMask.show();

		switch ( this.action ) {
			case 'add':
				this.setTitle(lang['sredniy_medpersonal_dobavlenie']);
				this.enableEdit(true);
			break;

			case 'edit':
			case 'view':
				var MedStaffFactLink_id = base_form.findField('MedStaffFactLink_id').getValue();

				if ( Ext.isEmpty(MedStaffFactLink_id) ) {
					loadMask.hide();
					this.hide();
					return false;
				}

				if ( this.action == 'edit' ) {
					this.setTitle(lang['sredniy_medpersonal_redaktirovanie']);
					this.enableEdit(true);

					base_form.findField('MedStaffFact_sid').disable();
					base_form.findField('MedStaffFactLink_begDT').disable();
				}
				else {
					this.setTitle(lang['sredniy_medpersonal_prosmotr']);
					this.enableEdit(false);
				}
			break;

			default:
				loadMask.hide();
				this.hide();
				return false;
			break;
		}

		loadMask.hide();

		base_form.findField('MedStaffFact_sid').fireEvent('change', base_form.findField('MedStaffFact_sid'), base_form.findField('MedStaffFact_sid').getValue());

		if ( this.action == 'add' || this.action == 'edit' ) {
			base_form.findField('MedStaffFact_sid').focus(true, 250);
		}
		else {
			this.buttons[this.buttons.length - 1].focus();
		}

		return true;
	},
	title: lang['sredniy_medpersonal'],
	width: 700
});
