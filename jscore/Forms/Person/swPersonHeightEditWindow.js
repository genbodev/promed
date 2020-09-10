/**
* swPersonHeightEditWindow - форма редактирования роста человека
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Person
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Stas Bykov aka Savage (savage1981@gmail.com)
* @version      27.12.2010
* @comment      Префикс для id компонентов PHEF (PersonHeightEditForm)
*/

sw.Promed.swPersonHeightEditWindow = Ext.extend(sw.Promed.BaseForm, {
	action: null,
	autoHeight: true,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	collapsible: false,
	doSave: function(options) {
		// options @Object
		// options.ignoreHeightIsIncorrect @Boolean Признак игнорирования проверки правильности ввода длины (роста)

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

		// Если не задан признак игнорирования проверки правильности ввода длины (роста)...
		if ( this.personMode == 'child' && (!options || !options.ignoreHeightIsIncorrect) ) {
			var height = base_form.findField('PersonHeight_Height').getValue();

			// ... и длина (рост) не в диапазоне 20-80...
			if ( Number(height) < 20 || Number(height) > 80 ) {
				// ... задаем вопрос
				sw.swMsg.show({
					buttons: Ext.Msg.YESNO,
					fn: function(buttonId, text, obj) {
						this.formStatus = 'edit';

						if ( buttonId == 'yes' ) {
							this.doSave({ ignoreHeightIsIncorrect: true });
						}
					}.createDelegate(this),
					icon: Ext.MessageBox.QUESTION,
					msg: lang['vozmojno_dlina_vvedena_ne_verno_sohranit'],
					title: lang['vopros']
				});
				return false;
			}
		}

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Подождите, идет сохранение..." });
		loadMask.show();

		var height_measure_type_code = 0;
		var height_measure_type_id = base_form.findField('HeightMeasureType_id').getValue();
		var index;
		var params = new Object();

		index = base_form.findField('HeightMeasureType_id').getStore().findBy(function(rec) {
			if ( rec.get('HeightMeasureType_id') == height_measure_type_id ) {
				return true;
			}
			else {
				return false;
			}
		});

		if ( index >= 0 ) {
			height_measure_type_code = base_form.findField('HeightMeasureType_id').getStore().getAt(index).get('HeightMeasureType_Code');
		}

		var data = new Object();

		switch ( this.formMode ) {
			case 'local':
				data.personHeightData = {
					'Evn_id': base_form.findField('Evn_id').getValue(),
					'PersonHeight_id': base_form.findField('PersonHeight_id').getValue(),
					'Person_id': base_form.findField('Person_id').getValue(),
					'PersonHeight_setDate': base_form.findField('PersonHeight_setDate').getValue(),
					'PersonHeight_Height': base_form.findField('PersonHeight_Height').getValue(),
					'PersonHeight_IsAbnorm': base_form.findField('PersonHeight_IsAbnorm').getValue(),
					'HeightAbnormType_id': base_form.findField('HeightAbnormType_id').getValue(),
					'HeightMeasureType_id': height_measure_type_id,
					'HeightMeasureType_Code': height_measure_type_code
				};

				this.callback(data);

				this.formStatus = 'edit';
				loadMask.hide();

				this.hide();
			break;

			case 'remote':
				base_form.submit({
					failure: function(result_form, action) {
						this.formStatus = 'edit';
						loadMask.hide();

						if ( action.result ) {
							if ( action.result.Error_Msg ) {
								sw.swMsg.alert(lang['oshibka'], action.result.Error_Msg);
							}
							else {
								sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_1]']);
							}
						}
					}.createDelegate(this),
					params: params,
					success: function(result_form, action) {
						this.formStatus = 'edit';
						loadMask.hide();

						if ( action.result ) {
							if ( action.result.PersonHeight_id > 0 ) {
								base_form.findField('PersonHeight_id').setValue(action.result.PersonHeight_id);

								data.personHeightData = {
									'Evn_id': base_form.findField('Evn_id').getValue(),
									'PersonHeight_id': base_form.findField('PersonHeight_id').getValue(),
									'Person_id': base_form.findField('Person_id').getValue(),
									'PersonHeight_setDate': base_form.findField('PersonHeight_setDate').getValue(),
									'PersonHeight_Height': base_form.findField('PersonHeight_Height').getValue(),
									'PersonHeight_IsAbnorm': base_form.findField('PersonHeight_IsAbnorm').getValue(),
									'HeightAbnormType_id': base_form.findField('HeightAbnormType_id').getValue(),
									'HeightMeasureType_id': base_form.findField('HeightMeasureType_id').getValue(),
									'HeightMeasureType_Code': height_measure_type_code
								};

								this.callback(data);
								this.hide();
							}
							else {
								if ( action.result.Error_Msg ) {
									sw.swMsg.alert(lang['oshibka'], action.result.Error_Msg);
								}
								else {
									sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_3]']);
								}
							}
						}
						else {
							sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_2]']);
						}
					}.createDelegate(this)
				});
			break;

			default:
				loadMask.hide();
			break;
		}
	},
	draggable: true,
	enableEdit: function(enable) {
		var base_form = this.FormPanel.getForm();
		var form_fields = new Array(
			'HeightMeasureType_id',
			'PersonHeight_Height',
			'PersonHeight_IsAbnorm',
			'PersonHeight_setDate'
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
	formMode: 'remote',
	formStatus: 'edit',
	id: 'PersonHeightEditWindow',
	initComponent: function() {
		this.FormPanel = new Ext.form.FormPanel({
			autoHeight: true,
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			border: false,
			frame: true,
			id: 'PersonHeightEditForm',
			labelAlign: 'right',
			labelWidth: 130,
			reader: new Ext.data.JsonReader({
				success: Ext.emptyFn
			},  [
				{ name: 'Evn_id' },
				{ name: 'HeightAbnormType_id' },
				{ name: 'HeightMeasureType_id' },
				{ name: 'PersonHeight_Height' },
				{ name: 'PersonHeight_id' },
				{ name: 'PersonHeight_IsAbnorm' },
				{ name: 'PersonHeight_setDate' },
				{ name: 'Person_id' },
				{ name: 'Server_id' }
			]),
			url: '/?c=PersonHeight&m=savePersonHeight',

			items: [{
				name: 'PersonHeight_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'Person_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'Evn_id',
				xtype: 'hidden'
			}, {
				name: 'Server_id',
				value: -1,
				xtype: 'hidden'
			}, {
				allowBlank: false,
				fieldLabel: lang['data_izmereniya'],
				format: 'd.m.Y',
				name: 'PersonHeight_setDate',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				selectOnFocus: true,
				tabIndex: TABINDEX_PHEF + 1,
				width: 100,
				xtype: 'swdatefield'
			}, {
				allowBlank: false,
				autoLoad: false,
				comboSubject: 'HeightMeasureType',
				fieldLabel: lang['vid_zamera'],
				hiddenName: 'HeightMeasureType_id',
				lastQuery: '',
				listeners: {
					'render': function(combo) {
						combo.getStore().load();
					}
				},
				tabIndex: TABINDEX_PHEF + 2,
				width: 350,
				xtype: 'swcommonsprcombo'
			}, {
				allowBlank: false,
				allowNegative: false,
				fieldLabel: lang['dlina_sm'],
				regex:new RegExp('(^[0-9]{0,3}\.[0-9]{0,2})$'),
				maxValue: 299.99,
				minValue: 1.00,
				name: 'PersonHeight_Height',
				tabIndex: TABINDEX_PHEF + 3,
				width: 100,
				xtype: 'numberfield'
			}, {
				allowBlank: true,
				comboSubject: 'YesNo',
				fieldLabel: lang['otklonenie'],
				hiddenName: 'PersonHeight_IsAbnorm',
				listeners: {
					'change': function(combo, newValue, oldValue) {
						var base_form = this.FormPanel.getForm();
						var record = combo.getStore().getById(newValue);

						if ( record && record.get('YesNo_Code') == 1 ) {
							base_form.findField('HeightAbnormType_id').enable();
						}
						else {
							base_form.findField('HeightAbnormType_id').clearValue();
							base_form.findField('HeightAbnormType_id').disable();
						}
					}.createDelegate(this)
				},
				tabIndex: TABINDEX_PHEF + 4,
				width: 100,
				xtype: 'swcommonsprcombo'
			}, {
				allowBlank: true,
				comboSubject: 'HeightAbnormType',
				fieldLabel: lang['tip'],
				hiddenName: 'HeightAbnormType_id',
				tabIndex: TABINDEX_PHEF + 5,
				width: 200,
				xtype: 'swcommonsprcombo'
			}]
		});

		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.doSave();
				}.createDelegate(this),
				iconCls: 'save16',
				onShiftTabAction: function () {
					var base_form = this.FormPanel.getForm();

					if ( this.action == 'view' ) {
						this.buttons[this.buttons.length - 1].focus(true);
					}
					else if ( !base_form.findField('HeightAbnormType_id').disabled ) {
						base_form.findField('HeightAbnormType_id').focus();
					}
					else {
						base_form.findField('PersonHeight_IsAbnorm').focus();
					}
				}.createDelegate(this),
				onTabAction: function () {
					this.buttons[this.buttons.length - 1].focus(true);
				}.createDelegate(this),
				tabIndex: TABINDEX_PHEF + 6,
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
					if ( !this.buttons[0].hidden ) {
						this.buttons[0].focus(true);
					}
				}.createDelegate(this),
				onTabAction: function () {
					if ( !this.FormPanel.getForm().findField('PersonHeight_setDate').disabled ) {
						this.FormPanel.getForm().findField('PersonHeight_setDate').focus(true);
					}
					else if ( !this.buttons[0].hidden ) {
						this.buttons[0].focus(true);
					}
				}.createDelegate(this),
				tabIndex: TABINDEX_PHEF + 7,
				text: BTN_FRMCANCEL
			}],
			items: [
				this.FormPanel
			],
			layout: 'form'
		});

		sw.Promed.swPersonHeightEditWindow.superclass.initComponent.apply(this, arguments);
	},
	keys: [{
		alt: true,
		fn: function(inp, e) {
			var current_window = Ext.getCmp('PersonHeightEditWindow');

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
		scope: this,
		stopEvent: true
	}],
	listeners: {
		'beforehide': function(win) {
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
		sw.Promed.swPersonHeightEditWindow.superclass.show.apply(this, arguments);

		this.center();

		var base_form = this.FormPanel.getForm();
		base_form.reset();

		this.action = null;
		this.callback = Ext.emptyFn;
		this.formMode = 'remote';
		this.formStatus = 'edit';
		this.measureTypeExceptions = new Array();
		this.onHide = Ext.emptyFn;
		this.personMode = 'man';

		if ( !arguments[0] || !arguments[0].formParams ) {
			sw.swMsg.alert(lang['soobschenie'], lang['nevernyie_parametryi'], function() { this.hide(); }.createDelegate(this) );
			return false;
		}

		base_form.findField('HeightMeasureType_id').getStore().clearFilter();
		base_form.findField('HeightMeasureType_id').lastQuery = '';

		base_form.setValues(arguments[0].formParams);

		if ( arguments[0].action ) {
			this.action = arguments[0].action;
		}

		if ( arguments[0].callback ) {
			this.callback = arguments[0].callback;
		}

		if ( arguments[0].formMode && typeof arguments[0].formMode == 'string' && arguments[0].formMode.inlist([ 'local', 'remote' ]) ) {
			this.formMode = arguments[0].formMode;
		}

		if ( arguments[0].measureTypeExceptions && typeof arguments[0].measureTypeExceptions == 'object' ) {
			this.measureTypeExceptions = arguments[0].measureTypeExceptions;
		}

		if ( arguments[0].onHide ) {
			this.onHide = arguments[0].onHide;
		}

		if ( arguments[0].personMode && typeof arguments[0].personMode == 'string' && arguments[0].personMode.inlist([ 'child', 'man' ]) ) {
			this.personMode = arguments[0].personMode;
		}

		base_form.findField('HeightAbnormType_id').disable();

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT });
		loadMask.show();

		var index;
		var record;

		switch ( this.action ) {
			case 'add':
				this.setTitle(WND_PERSHEIGHT_ADD);
				this.enableEdit(true);

				if ( this.measureTypeExceptions.length > 0 ) {
					base_form.findField('HeightMeasureType_id').getStore().filterBy(function(rec) {
						if ( rec.get('HeightMeasureType_Code').toString().inlist(this.measureTypeExceptions) ) {
							return false;
						}
						else {
							return true;
						}
					}.createDelegate(this));

					if ( base_form.findField('HeightMeasureType_id').getStore().getCount() == 1 ) {
						base_form.findField('HeightMeasureType_id').setValue(base_form.findField('HeightMeasureType_id').getStore().getAt(0).get('HeightMeasureType_id'));
					}
				}

				base_form.findField('PersonHeight_IsAbnorm').setValue(1);

				loadMask.hide();

				//если передали дату - устанавливаю ее.
				if ( (arguments[0].PersonHeight_setDate != undefined) && (arguments[0].PersonHeight_setDate != null)) {
					base_form.findField('PersonHeight_setDate').setValue(arguments[0].PersonHeight_setDate);
				} else {
					//если не передали - устанавливаю текущую
					setCurrentDateTime({
						callback: function() {
							base_form.findField('PersonHeight_setDate').focus(true, 250);
						}.createDelegate(this),
						dateField: base_form.findField('PersonHeight_setDate'),
						loadMask: true,
						setDate: true,
						setDateMaxValue: true,
						windowId: this.id
					});
				}

			break;

			case 'edit':
			case 'view':
				if ( this.formMode == 'local' ) {
					if ( this.action == 'edit' ) {
						this.setTitle(WND_PERSHEIGHT_EDIT);
						this.enableEdit(true);
					}
					else {
						this.setTitle(WND_PERSHEIGHT_VIEW);
						this.enableEdit(false);
					}

					if ( this.action == 'edit' ) {
						setCurrentDateTime({
							dateField: base_form.findField('PersonHeight_setDate'),
							loadMask: true,
							setDate: false,
							setDateMaxValue: true,
							windowId: this.id
						});

						var record = base_form.findField('PersonHeight_IsAbnorm').getStore().getById(base_form.findField('PersonHeight_IsAbnorm').getValue());

						if ( record && record.get('YesNo_Code') == 1 ) {
							base_form.findField('HeightAbnormType_id').enable();
						}
						else {
							base_form.findField('HeightAbnormType_id').clearValue();
							base_form.findField('HeightAbnormType_id').disable();
						}

						if ( this.measureTypeExceptions.length > 0 ) {
							base_form.findField('HeightMeasureType_id').getStore().filterBy(function(rec) {
								if ( rec.get('HeightMeasureType_Code').toString().inlist(this.measureTypeExceptions) && rec.get('HeightMeasureType_id') != base_form.findField('HeightMeasureType_id').getValue() ) {
									return false;
								}
								else {
									return true;
								}
							}.createDelegate(this));
						}
					}

					loadMask.hide();

					if ( this.action == 'edit' ) {
						base_form.findField('PersonHeight_setDate').focus(true, 250);
					}
					else {
						this.buttons[this.buttons.length - 1].focus();
					}
				}
				else {
					var person_height_id = base_form.findField('PersonHeight_id').getValue();

					if ( !person_height_id ) {
						loadMask.hide();
						this.hide();
						return false;
					}

					base_form.load({
						failure: function() {
							loadMask.hide();
							sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_zagruzke_dannyih_formyi'], function() { this.hide(); }.createDelegate(this) );
						}.createDelegate(this),
						params: {
							'PersonHeight_id': person_height_id
						},
						success: function() {
							if ( this.action == 'edit' ) {
								this.setTitle(WND_PERSHEIGHT_EDIT);
								this.enableEdit(true);
							}
							else {
								this.setTitle(WND_PERSHEIGHT_VIEW);
								this.enableEdit(false);
							}

							loadMask.hide();

							if ( this.action == 'view' ) {
								this.buttons[this.buttons.length - 1].focus();
							}
							else {
								base_form.findField('PersonHeight_setDate').focus(true, 250);
							}
						}.createDelegate(this),
						url: '/?c=PersonHeight&m=loadPersonHeightEditForm'
					});
				}
			break;

			default:
				loadMask.hide();
				this.hide();
			break;
		}
	},
	width: 600
});