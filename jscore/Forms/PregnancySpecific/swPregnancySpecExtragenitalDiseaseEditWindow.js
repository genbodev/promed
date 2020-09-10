/**
 * swPregnancySpecExtragenitalDiseaseEditWindow - Редактирование экстрагенитальных заболеваний в специфике беременности карты ДУ
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package	  Person
 * @access	   public
 * @copyright	Copyright (c) 2009 Swan Ltd.
 * @author	   gabdushev
 * @version	  27.12.2010
 * @comment	  Префикс для id компонентов PSEDEF (PregnancySpecExtragenitalDiseaseEditForm)
 */

sw.Promed.swPregnancySpecExtragenitalDiseaseEditWindow = Ext.extend(sw.Promed.BaseForm, {
	action: null,
	autoHeight: true,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	collapsible: false,
	doSave: function() {
		// options @Object
		// options.ignoreHeightIsIncorrect @Boolean Признак игнорирования проверки правильности ввода длины (роста)
		if (this.formStatus == 'save') {
			return false;
		}
		this.formStatus = 'save';
		var form = this.FormPanel;
		var base_form = form.getForm();
		if (!base_form.isValid()) {
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
		for (var k in this.gridRecords) {
			if (!Ext.isEmpty(this.gridRecords[k].Diag_id) && this.gridRecords[k].Diag_id == base_form.findField('Diag_id').getValue() && this.gridRecords[k].PSED_setDT == Ext.util.Format.date(base_form.findField('PSEDEF_PSED_setDT').getValue(), 'd.m.Y') && this.gridRecords[k].PSED_id != base_form.findField('PSEDEF_PSED_id').getValue()) {
				sw.swMsg.alert(lang['oshibka'], lang['v_spiske_uje_est_ravnoznachnaya_zapis'], function() { this.formStatus = 'edit'; }.createDelegate(this) );
				return false;
			}
		}
		var data = new Object();
		data.ExtragenitalDiseaseData = {
			'PSED_id'   : base_form.findField('PSEDEF_PSED_id').getValue(),
			'PregnancySpec_id'                      : base_form.findField('PSEDEF_PregnancySpec_id').getValue(),
			'PSED_setDT': base_form.findField('PSEDEF_PSED_setDT').getValue(),
			'Diag_id'                               : base_form.findField('Diag_id').getValue(),
			'Diag_Name'                             : base_form.findField('Diag_id').getRawValue()
		};
		this.callback(data);
		this.hide();
		return true;
	},
	draggable: true,
	enableEdit: function(enable) {
		var base_form = this.FormPanel.getForm();
		var form_fields = new Array('PSEDEF_PSED_id', 'PSEDEF_PregnancySpec_id', 'PSEDEF_PSED_setDT', 'PSEDEF_DiagCombo');
		var i = 0;
		for (i = 0; i < form_fields.length; i++) {
			if (enable) {
				base_form.findField(form_fields[i]).enable();
			} else {
				base_form.findField(form_fields[i]).disable();
			}
		}
		if (enable) {
			this.buttons[0].show();
		} else {
			this.buttons[0].hide();
		}
	},
	formStatus: 'edit',
	id: 'PregnancySpecExtragenitalDiseaseEditWindow',
	initComponent: function() {
		this.FormPanel = new Ext.form.FormPanel({
			autoHeight: true,
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			border: false,
			frame: true,
			id: 'PregnancySpecExtragenitalDiseaseEditForm',
			labelAlign: 'right',
			labelWidth: 130,
			items: [
				{
					name: 'PSEDEF_PSED_id',
					value: 0,
					xtype: 'hidden'
				},
				{
					name: 'PSEDEF_PregnancySpec_id',
					value: 0,
					xtype: 'hidden'
				},
				{
					allowBlank: false,
					fieldLabel: lang['data_ustanovki'],
					format: 'd.m.Y',
					listeners: {
						'keydown': function(inp, e) {
							if ((e.getKey() == Ext.EventObject.TAB) && e.shiftKey ) {
								e.stopEvent();
								this.buttons[this.buttons.length - 1].focus();
							}
						}.createDelegate(this)
					},
					name: 'PSEDEF_PSED_setDT',
					plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
					selectOnFocus: true,
					tabIndex: TABINDEX_PSEDEF + 1,
					width: 100,
					xtype: 'swdatefield'
				},
				{
					allowBlank: false,
					fieldLabel: lang['diagnoz'],
					hiddenName: 'Diag_id',
					id: 'PSEDEF_DiagCombo',
					tabIndex: TABINDEX_PSEDEF + 2,
					width: 400,
					xtype: 'swdiagcombo'
				}
			]
		});
		var current_window = this;
		Ext.apply(this, {
			buttons: [
				{
					handler: function() {
						this.doSave();
					}.createDelegate(this),
					iconCls: 'save16',
					onShiftTabAction: function () {
						var base_form = this.FormPanel.getForm();
						if (this.action == 'view') {
							this.buttons[this.buttons.length - 1].focus(true);
						} else {
							base_form.findField('Diag_id').focus();
						}
					}.createDelegate(this),
					onTabAction: function () {
						this.buttons[this.buttons.length - 1].focus(true);
					}.createDelegate(this),
					tabIndex: TABINDEX_PSEDEF + 3,
					text: BTN_FRMSAVE
				},
				{
					text: '-'
				},
				HelpButton(this, -1),
				{
					handler: function() {
						this.hide();
					}.createDelegate(this),
					iconCls: 'cancel16',
					onShiftTabAction: function () {
						if (!this.buttons[0].hidden) {
							this.buttons[0].focus(true);
						}
					}.createDelegate(this),
					onTabAction: function () {
						if (!this.FormPanel.getForm().findField('PSEDEF_PSED_setDT').disabled) {
							this.FormPanel.getForm().findField('PSEDEF_PSED_setDT').focus(true);
						} else if (!this.buttons[0].hidden) {
							this.buttons[0].focus(true);
						}
					}.createDelegate(this),
					tabIndex: TABINDEX_PSEDEF + 4,
					text: BTN_FRMCANCEL
				}
			],
			items: [
				this.FormPanel
			],
			layout: 'form',
			keys: [
				{
					alt: true,
					fn: function(inp, e) {
						switch (e.getKey()) {
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
					scope: this,
					stopEvent: true
				}
			]
		});
		sw.Promed.swPregnancySpecExtragenitalDiseaseEditWindow.superclass.initComponent.apply(this, arguments);
	},
	listeners: {
		'beforehide': function() {
			// 
		},
		'hide': function(win) {
			win.onHide();
		}
	},
	maximizable: false,
	maximized: false,
	modal: true,
	onHide: Ext.emptyFn,
	plain: true,
	resizable: false,
	show: function() {
		sw.Promed.swPregnancySpecExtragenitalDiseaseEditWindow.superclass.show.apply(this, arguments);
		this.center();
		var base_form = this.FormPanel.getForm();
		base_form.reset();
		this.action = null;
		this.callback = Ext.emptyFn;
		this.formStatus = 'edit';
		this.measureTypeExceptions = new Array();
		this.onHide = Ext.emptyFn;
		this.gridRecords = [];
		
		if (!arguments[0] || !arguments[0].formParams) {
			sw.swMsg.alert(lang['soobschenie'], lang['nevernyie_parametryi'], function() {
				this.hide();
			}.createDelegate(this));
			return false;
		}
		if (arguments[0].action) {
			this.action = arguments[0].action;
		}
		if (arguments[0].callback) {
			this.callback = arguments[0].callback;
		}
		if (arguments[0].onHide) {
			this.onHide = arguments[0].onHide;
		}
		if (arguments[0].gridRecords) {
			this.gridRecords = arguments[0].gridRecords;
		}
		switch (this.action) {
			case 'add':
				this.setTitle(lang['ekstragenitalnyie_zabolevaniya_dobavlenie']);
				this.enableEdit(true);
				setCurrentDateTime({
					callback: function() {
						base_form.findField('PSEDEF_PSED_setDT').focus(true, 250);
					}.createDelegate(this),
					dateField: base_form.findField('PSEDEF_PSED_setDT'),
					loadMask: true,
					setDate: true,
					setDateMaxValue: true,
					windowId: this.id
				});
				break;
			case 'edit':
			case 'view':
				base_form.findField('PSEDEF_PSED_id').setValue(arguments[0].formParams['PSED_id']),
				base_form.findField('PSEDEF_PregnancySpec_id').setValue(arguments[0].formParams['PregnancySpec_id']);
				base_form.findField('PSEDEF_PSED_setDT').setValue(arguments[0].formParams['PSED_setDT']);
				var diag_id = arguments[0].formParams['Diag_id'];
				if (diag_id != null && diag_id.toString().length > 0) {
					base_form.findField('Diag_id').getStore().load({
						callback: function() {
							base_form.findField('Diag_id').getStore().each(function(record) {
								if (record.get('Diag_id') == diag_id) {
									base_form.findField('Diag_id').setValue(record.get('Diag_id'));
									base_form.findField('Diag_id').fireEvent('select', base_form.findField('Diag_id'), record, 0);
								}
							});
						},
						params: { where: "where DiagLevel_id = 4 and Diag_id = " + diag_id }
					});
				}
				
				if (this.action == 'edit') {
					this.setTitle(lang['ekstragenitalnyie_zabolevaniya_redaktirovanie']);
					this.enableEdit(true);
					base_form.findField('PSEDEF_PSED_setDT').focus(true, 250);
				} else {			
					this.setTitle(lang['ekstragenitalnyie_zabolevaniya_prosmotr']);
					this.enableEdit(false);
					this.buttons[this.buttons.length - 1].focus();
				}
				break;
			default:
				this.hide();
				break;
		}
	},
	width: 600
});