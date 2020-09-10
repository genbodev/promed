/**
* swUslugaComplexListEditWindow - окно редактирования/добавления услуги для комплексной услуги.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Stas Bykov aka Savage (savage1981@gmail.com)
* @version      0.001-27.02.2009
* @comment      Префикс для id компонентов USLEF (UslugaComplexListEditForm)
*               tabIndex: ???
*
*
* @input data: action - действие (add, edit, view)
*/

sw.Promed.swUslugaComplexListEditWindow = Ext.extend(sw.Promed.BaseForm, {
	action: null,
	autoHeight: true,
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	doSave: function() {
		if ( this.formStatus == 'save' || this.action == 'view' ) {
			return false;
		}

		this.formStatus = 'save';

		var form = this.findById('UslugaComplexListEditForm');
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

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT_SAVE });
		loadMask.show();

		base_form.submit({
			failure: function(result_form, action) {
				this.formStatus = 'edit';
				loadMask.hide();

				if ( action.result ) {
					if ( action.result.Error_Msg ) {
						sw.swMsg.alert(lang['oshibka'], action.result.Error_Msg);
					}
					else {
						sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_3]']);
					}
				}
			}.createDelegate(this),
			success: function(result_form, action) {
				this.formStatus = 'edit';
				loadMask.hide();

				if ( action.result && action.result.UslugaComplexList_id > 0 ) {
					base_form.findField('UslugaComplexList_id').setValue(action.result.UslugaComplexList_id);

					var data = new Object();

					var usluga_class_id = base_form.findField('UslugaClass_id').getValue();
					var usluga_class_name = '';
					var usluga_code = '';
					var usluga_id = base_form.findField('Usluga_id').getValue();
					var usluga_name = '';

					var record = base_form.findField('Usluga_id').getStore().getById(usluga_id);

					if ( record ) {
						usluga_code = record.get('Usluga_Code');
						usluga_name = record.get('Usluga_Name');
					}

					record = base_form.findField('UslugaClass_id').getStore().getById(usluga_class_id);

					if ( record ) {
						usluga_class_name = record.get('UslugaClass_Name');
					}

					data.UslugaComplexListData = [{
						'UslugaComplexList_id': base_form.findField('UslugaComplexList_id').getValue(),
						'UslugaComplex_id': base_form.findField('UslugaComplex_id').getValue(),
						'Usluga_id': usluga_id,
						'UslugaClass_id': usluga_class_id,
						'Usluga_Code': usluga_code,
						'Usluga_Name': usluga_name,
						'UslugaClass_Name': usluga_class_name
					}];

					this.callback(data);
					this.hide();
				}
				else {
					sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_2]']);
				}
			}.createDelegate(this)
		});
	},
	draggable: true,
	enableEdit: function(enable) {
		var base_form = this.findById('UslugaComplexListEditForm').getForm();

		if ( enable ) {
			base_form.findField('Usluga_id').enable();
			this.buttons[0].show();
		}
		else {
			base_form.findField('Usluga_id').disable();
			this.buttons[0].hide();
		}
	},
	formStatus: 'edit',
	id: 'UslugaComplexListEditWindow',
	initComponent: function() {
		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.doSave();
				}.createDelegate(this),
				iconCls: 'save16',
				onShiftTabAction: function () {
					if ( !this.findById('UslugaComplexListEditForm').getForm().findField('UslugaClass_id').disabled ) {
						this.findById('UslugaComplexListEditForm').getForm().findField('UslugaClass_id').focus(true);
					}
					else {
						this.findById('UslugaComplexListEditForm').getForm().findField('Usluga_id').focus(true);
					}
				}.createDelegate(this),
				onTabAction: function () {
					this.buttons[this.buttons.length - 1].focus();
				}.createDelegate(this),
				// tabIndex: 3005,
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
				id: 'USLEF_CancelButton',
				onShiftTabAction: function () {
					if ( this.action != 'view' ) {
						this.buttons[0].focus();
					}
				}.createDelegate(this),
				onTabAction: function () {
					if ( this.action != 'view' ) {
						this.findById('UslugaComplexListEditForm').getForm().findField('Usluga_id').focus(true);
					}
				}.createDelegate(this),
				// tabIndex: 3006,
				text: BTN_FRMCANCEL
			}],
			items: [ new Ext.form.FormPanel({
				bodyBorder: false,
				bodyStyle: 'padding: 5px 5px 0',
				border: false,
				buttonAlign: 'left',
				frame: false,
				id: 'UslugaComplexListEditForm',
				labelAlign: 'right',
				labelWidth: 120,
				items: [{
					name: 'UslugaComplexList_id',
					value: 0,
					xtype: 'hidden'
				}, {
					name: 'UslugaComplex_id',
					value: 0,
					xtype: 'hidden'
				}, {
					allowBlank: false,
					fieldLabel: lang['usluga'],
					hiddenName: 'Usluga_id',
					focusOnShiftTab: 'USLEF_CancelButton',
					listeners: {
						'select': function(combo, record, index) {
							var base_form = this.findById('UslugaComplexListEditForm').getForm();

 							if ( record.get(combo.valueField) ) {
								combo.setRawValue(record.get('Usluga_Code') + ". " + record.get('Usluga_Name'));
							}

							if ( record && record.get('Usluga_Code') ) {
								var usluga_class_id = base_form.findField('UslugaClass_id').getValue();

								if ( this.action == 'view' ) {
									base_form.findField('UslugaClass_id').disable();
								}
								else {
									base_form.findField('UslugaClass_id').enable();
								}

								if ( record.get('Usluga_Code').toString().substr(0, 2) == '04' ) {
									base_form.findField('UslugaClass_id').getStore().load({
										params: {
											where: 'where UslugaClass_Code in (1, 7)'
										}
									});
								}
								else {
									base_form.findField('UslugaClass_id').getStore().load({
										params: {
											where: 'where UslugaClass_Code in (5, 6, 7)'
										}
									});
								}

								record = base_form.findField('UslugaClass_id').getStore().getById(usluga_class_id);

								if ( record ) {
									base_form.findField('UslugaClass_id').setValue(usluga_class_id);
								}
								else {
									base_form.findField('UslugaClass_id').clearValue();
								}
							}
							else {
								base_form.findField('UslugaClass_id').clearValue();
								base_form.findField('UslugaClass_id').disable();
							}
						}.createDelegate(this),
						'blur': function(combo) {
							combo.dqTask.cancel();
							combo.collapse();

							if ( combo.getRawValue() == '' ) {
								combo.setValue('');
							}
							else {
								var patt = new RegExp("^[0-9]+");
								var store = combo.getStore();
								var val = '';

								var sr = patt.exec(combo.getRawValue().toString());

								if ( sr == null ) {
									val = '';
								}
								else {
									val = sr[0];
								}

								combo.getStore().load({
									callback: function(rec, opt) {
										if ( rec.length > 0 && rec[0].get('Usluga_id') != '' ) {
											this.setValue(rec[0].get(this.valueField));
											this.setRawValue(rec[0].get('Usluga_Code') + ". " + rec[0].get('Usluga_Name'));
										}
										else {
											this.setValue('');
											this.setRawValue('');
										}
									}.createDelegate(combo),
									params: {
										where: "where UslugaType_id = 2 and Usluga_Code like '" + val + "%' limit 100"
									}
								});
							}
						},
						'keydown': function(inp, e) {
							if ( e.getKey() == e.END) {
								inp.inKeyMode = true;
								inp.select(inp.store.getCount() - 1);
							}

							if ( e.getKey() == e.HOME) {
								inp.inKeyMode = true;
								inp.select(0);
							}

							if ( e.getKey() == e.PAGE_UP) {
								inp.inKeyMode = true;
								var ct = inp.store.getCount();

								if ( ct > 0 ) {
									if ( inp.selectedIndex == -1 ) {
										inp.select(0);
									}
									else if ( inp.selectedIndex != 0 ) {
										if ( inp.selectedIndex - 10 >= 0 )
											inp.select(inp.selectedIndex - 10);
										else
											inp.select(0);
									}
								}
							}

							if ( e.getKey() == e.PAGE_DOWN) {
								if ( !inp.isExpanded() ) {
									inp.onTriggerClick();
								}
								else {
									inp.inKeyMode = true;
									var ct = inp.store.getCount();

									if ( ct > 0 ) {
										if ( inp.selectedIndex == -1 ) {
											inp.select(0);
										}
										else if ( inp.selectedIndex != ct - 1 ) {
											if ( inp.selectedIndex + 10 < ct - 1 )
												inp.select(inp.selectedIndex + 10);
											else
												inp.select(ct - 1);
										}
									}
								}
							}

							if ( e.shiftKey == false && e.getKey() == Ext.EventObject.TAB && inp.focusOnTab != null && inp.focusOnTab.toString().length > 0 ) {
								e.stopEvent();

								if ( Ext.getCmp(inp.focusOnTab) ) {
									Ext.getCmp(inp.focusOnTab).focus(true);
								}
							}

							if ( e.shiftKey == true && e.getKey() == Ext.EventObject.TAB && inp.focusOnShiftTab != null && inp.focusOnShiftTab.toString().length > 0 ) {
								e.stopEvent();

								if ( Ext.getCmp(inp.focusOnShiftTab) ) {
									Ext.getCmp(inp.focusOnShiftTab).focus(true);
								}
							}

							if ( e.altKey || e.ctrlKey || e.shiftKey )
								return true;

							if ( e.getKey() == e.DELETE) {
								inp.setValue('');
								inp.setRawValue("");
								inp.selectIndex = -1;
								if ( inp.onClearValue ) {
									inp.onClearValue();
								}
								e.stopEvent();
								return true;
							}

							if ( e.getKey() == e.F4 ) {
								inp.onTrigger2Click();
							}
						}
					},
					// tabIndex: 3001,
					width: 450,
					xtype: 'swuslugacombo'
				}, {
					allowBlank: false,
					fieldLabel: lang['klacc_uslugi'],
					lastQuery: '',
					// tabIndex: 3001,
					width: 250,
					xtype: 'swuslugaclasscombo'
				}],
				layout: 'form',
				url: '/?c=EvnUsluga&m=saveUslugaComplexList'
			})]
		});
		sw.Promed.swUslugaComplexListEditWindow.superclass.initComponent.apply(this, arguments);
	},
	keys: [{
		alt: true,
		fn: function(inp, e) {
			var current_window = Ext.getCmp('UslugaComplexListEditWindow');

			switch ( e.getKey() ) {
				case Ext.EventObject.C:
					if ( current_window.action != 'view' ) {
						current_window.doSave();
					}
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
		'hide': function() {
			this.onHide();
		}
	},
	maximizable: false,
	modal: true,
	onHide: Ext.emptyFn,
	plain: true,
	resizable: false,
	show: function() {
		sw.Promed.swUslugaComplexListEditWindow.superclass.show.apply(this, arguments);

		var base_form = this.findById('UslugaComplexListEditForm').getForm();

		this.center();
		base_form.reset();

		this.action = null;
		this.callback = Ext.emptyFn;
		this.formStatus = 'edit';
		this.onHide = Ext.emptyFn;

		base_form.findField('UslugaClass_id').getStore().removeAll();

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
				this.setTitle(lang['usluga_dobavlenie']);
				this.enableEdit(true);

				base_form.findField('UslugaClass_id').disable();

				base_form.findField('Usluga_id').focus(false, 250);

				loadMask.hide();
			break;

			case 'edit':
				this.setTitle(lang['usluga_redaktirovanie']);
				this.enableEdit(true);

				base_form.findField('UslugaClass_id').enable();

				base_form.findField('Usluga_id').getStore().load({
					callback: function(r, o, s) {
						base_form.findField('Usluga_id').getStore().each(function(record) {
							if ( record.get('Usluga_id') == base_form.findField('Usluga_id').getValue() ) {
								base_form.findField('Usluga_id').fireEvent('select', base_form.findField('Usluga_id'), record, 0);
							}
						});

						base_form.findField('Usluga_id').focus(true, 100);
					},
					params: {
						where: "where UslugaType_id = 2 and Usluga_id = " + base_form.findField('Usluga_id').getValue()
					}
				});

				loadMask.hide();
			break;

			case 'view':
				this.setTitle(lang['usluga_prosmotr']);
				this.enableEdit(false);

				base_form.findField('UslugaClass_id').disable();

				base_form.findField('Usluga_id').getStore().load({
					callback: function(r, o, s) {
						base_form.findField('Usluga_id').getStore().each(function(record) {
							if ( record.get('Usluga_id') == base_form.findField('Usluga_id').getValue() ) {
								base_form.findField('Usluga_id').fireEvent('select', base_form.findField('Usluga_id'), record, 0);
							}
						});

						this.buttons[this.buttons.length - 1].focus();
					}.createDelegate(this),
					params: {
						where: "where UslugaType_id = 2 and Usluga_id = " + base_form.findField('Usluga_id').getValue()
					}
				});

				loadMask.hide();
			break;
		}

		base_form.clearInvalid();
	},
	width: 650
});