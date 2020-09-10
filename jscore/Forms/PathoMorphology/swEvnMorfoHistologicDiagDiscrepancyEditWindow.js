/**
* swEvnMorfoHistologicDiagDiscrepancyEditWindow - ошибка клинической диагностики
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      PathoMorphology
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Stas Bykov aka Savage (savage1981@gmail.com)
* @version      16.02.2011
* @comment      Префикс для id компонентов EMHDDEF (EvnMorfoHistologicDiagDiscrepancyEditForm)
*/

sw.Promed.swEvnMorfoHistologicDiagDiscrepancyEditWindow = Ext.extend(sw.Promed.BaseForm, {
	action: null,
	autoHeight: true,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	collapsible: false,
	doSave: function() {
		if ( this.formStatus == 'save' ) {
			return false;
		}

		this.formStatus = 'save';

		var form = this.FormPanel;
		var base_form = form.getForm();

		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					this.formStatus = 'edit';
					form.getFirstInvalidEl().focus(false);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Подождите, идет сохранение..." });
		loadMask.show();

		var diag_clinical_err_type_id = base_form.findField('DiagClinicalErrType_id').getValue();
		var diag_clinical_err_type_name = '';
		var diag_reason_discrepancy_id = base_form.findField('DiagReasonDiscrepancy_id').getValue();
		var diag_reason_discrepancy_name = '';
		var index;
		var params = new Object();

		index = base_form.findField('DiagClinicalErrType_id').getStore().findBy(function(rec) {
			if ( rec.get('DiagClinicalErrType_id') == diag_clinical_err_type_id ) {
				return true;
			}
			else {
				return false;
			}
		});

		if ( index >= 0 ) {
			diag_clinical_err_type_name = base_form.findField('DiagClinicalErrType_id').getStore().getAt(index).get('DiagClinicalErrType_Name');
		}

		index = base_form.findField('DiagReasonDiscrepancy_id').getStore().findBy(function(rec) {
			if ( rec.get('DiagReasonDiscrepancy_id') == diag_reason_discrepancy_id ) {
				return true;
			}
			else {
				return false;
			}
		});

		if ( index >= 0 ) {
			diag_reason_discrepancy_name = base_form.findField('DiagReasonDiscrepancy_id').getStore().getAt(index).get('DiagReasonDiscrepancy_Name');
		}

		var data = new Object();

		data.evnMorfoHistologicDiagDiscrepancyData = {
			'EvnMorfoHistologicDiagDiscrepancy_id': base_form.findField('EvnMorfoHistologicDiagDiscrepancy_id').getValue(),
			'EvnMorfoHistologicProto_id': base_form.findField('EvnMorfoHistologicProto_id').getValue(),
			'DiagClinicalErrType_id': diag_clinical_err_type_id,
			'DiagReasonDiscrepancy_id': diag_reason_discrepancy_id,
			'EvnMorfoHistologicDiagDiscrepancy_Note': base_form.findField('EvnMorfoHistologicDiagDiscrepancy_Note').getValue(),
			'DiagClinicalErrType_Name': diag_clinical_err_type_name,
			'DiagReasonDiscrepancy_Name': diag_reason_discrepancy_name
		};

		this.callback(data);

		this.formStatus = 'edit';
		loadMask.hide();

		if ( this.action == 'add' ) {
			sw.swMsg.show({
				buttons: Ext.Msg.YESNO,
				fn: function(buttonId, text, obj) {
					if ( 'yes' == buttonId ) {
						base_form.findField('DiagReasonDiscrepancy_id').clearValue();
						base_form.findField('EvnMorfoHistologicDiagDiscrepancy_Note').setRawValue('');

						base_form.findField('DiagReasonDiscrepancy_id').focus();
					}
					else {
						this.hide();
					}
				}.createDelegate(this),
				icon: Ext.MessageBox.QUESTION,
				msg: lang['dobavit_esche_odnu_oshibku'],
				title: lang['vopros']
			});
		}
		else {
			this.hide();
		}
	},
	draggable: true,
	enableEdit: function(enable) {
		var base_form = this.FormPanel.getForm();
		var form_fields = new Array(
			'DiagClinicalErrType_id',
			'DiagReasonDiscrepancy_id',
			'EvnMorfoHistologicDiagDiscrepancy_Note'
		);
		var i = 0;

		for ( i = 0; i < form_fields.length; i++ ) {
			if ( enable ) {
				base_form.findField(form_fields[i]).enable();
			}
			else {
				base_form.findField(form_fields[i]).disable();
			}
		}

		if ( enable ) {
			this.buttons[0].show();
		}
		else {
			this.buttons[0].hide();
		}
	},
	formStatus: 'edit',
	id: 'EvnMorfoHistologicDiagDiscrepancyEditWindow',
	initComponent: function() {
		this.FormPanel = new Ext.form.FormPanel({
			autoHeight: true,
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			border: false,
			frame: false,
			id: 'EvnMorfoHistologicDiagDiscrepancyEditForm',
			labelAlign: 'right',
			labelWidth: 250,
			reader: new Ext.data.JsonReader({
				success: Ext.amptyFn
			},  [
				{ name: 'accessType' },
				{ name: 'EvnMorfoHistologicDiagDiscrepancy_id' },
				{ name: 'EvnMorfoHistologicProto_id' },
				{ name: 'DiagClinicalErrType_id' },
				{ name: 'DiagReasonDiscrepancy_id' },
				{ name: 'EvnMorfoHistologicDiagDiscrepancy_Note' }
			]),
			url: '/?c=EvnMorfoHistologicProto&m=saveEvnMorfoHistologicDiagDiscrepancy',

			items: [{
				name: 'accessType',
				value: '',
				xtype: 'hidden'
			}, {
				name: 'EvnMorfoHistologicDiagDiscrepancy_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'EvnMorfoHistologicProto_id',
				value: 0,
				xtype: 'hidden'
			}, {
				allowBlank: false,
				comboSubject: 'DiagClinicalErrType',
				fieldLabel: lang['tip_oshibki_klinicheskoy_diagnostiki'],
				hiddenName: 'DiagClinicalErrType_id',
				tabIndex: TABINDEX_EMHDDEF + 1,
				width: 400,
				xtype: 'swcommonsprcombo'
			}, {
				allowBlank: false,
				comboSubject: 'DiagReasonDiscrepancy',
				fieldLabel: lang['prichina_rashojdeniya_diagnoza'],
				hiddenName: 'DiagReasonDiscrepancy_id',
				tabIndex: TABINDEX_EMHDDEF + 2,
				width: 400,
				xtype: 'swcommonsprcombo'
			}, {
				allowBlank: true,
				fieldLabel: lang['primechanie'],
				height: 50,
				name: 'EvnMorfoHistologicDiagDiscrepancy_Note',
				tabIndex: TABINDEX_EMHDDEF + 3,
				width: 400,
				xtype: 'textarea'
			}]
		});

		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.doSave();
				}.createDelegate(this),
				iconCls: 'save16',
				onShiftTabAction: function () {
					if ( this.action == 'view' ) {
						this.buttons[this.buttons.length - 1].focus(true);
					}
					else {
						this.FormPanel.getForm().findField('EvnMorfoHistologicDiagDiscrepancy_Note').focus(true);
					}
				}.createDelegate(this),
				onTabAction: function () {
					this.buttons[this.buttons.length - 1].focus(true);
				}.createDelegate(this),
				tabIndex: TABINDEX_EMHDDEF + 4,
				text: BTN_FRMSAVE
			}, {
				text: '-'
			},
			HelpButton(this, -1),
			{
				handler: function() {
					this.hide();
				}.createDelegate(this),
				iconCls: 'cancel16',
				onShiftTabAction: function () {
					if ( this.action != 'view' ) {
						this.buttons[0].focus();
					}
				}.createDelegate(this),
				onTabAction: function () {
					if ( this.action != 'view' ) {
						this.FormPanel.getForm().findField('DiagClinicalErrType_id').focus(true);
					}
				}.createDelegate(this),
				tabIndex: TABINDEX_EMHDDEF + 5,
				text: BTN_FRMCANCEL
			}],
			items: [
				this.FormPanel
			],
			layout: 'form'
		});

		sw.Promed.swEvnMorfoHistologicDiagDiscrepancyEditWindow.superclass.initComponent.apply(this, arguments);
	},
	keys: [{
		alt: true,
		fn: function(inp, e) {
			var current_window = Ext.getCmp('EvnMorfoHistologicDiagDiscrepancyEditWindow');

			switch ( e.getKey() ) {
				case Ext.EventObject.C:
					current_window.doSave();
				break;

				case Ext.EventObject.J:
					current_window.hide();
				break;
			}
		},
		key: [
			Ext.EventObject.C,
			Ext.EventObject.J
		],
		stopEvent: true
	}],
	layout: 'form',
	listeners: {
		'hide': function(win) {
			win.onHide();
		}
	},
	maximizable: false,
	modal: true,
	onHide: Ext.emptyFn,
	plain: true,
	resizable: false,
	show: function() {
		sw.Promed.swEvnMorfoHistologicDiagDiscrepancyEditWindow.superclass.show.apply(this, arguments);

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

		var index;
		var record;

		switch ( this.action ) {
			case 'add':
				this.setTitle(WND_PATHOMORPH_EMHDDEFADD);
				this.enableEdit(true);
			break;

			case 'edit':
				this.setTitle(WND_PATHOMORPH_EMHDDEFEDIT);
				this.enableEdit(true);
			break;

			case 'view':
				this.setTitle(WND_PATHOMORPH_EMHDDEFVIEW);
				this.enableEdit(false);
			break;

			default:
				loadMask.hide();
				this.hide();
			break;
		}

		loadMask.hide();

		base_form.clearInvalid();

		if ( this.action == 'view' ) {
			this.buttons[this.buttons.length - 1].focus();
		}
		else {
			base_form.findField('DiagClinicalErrType_id').focus(true, 250);
		}
	},
	width: 700
});