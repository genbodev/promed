/**
* swEvnHistologicMicroEditWindow - микроскопическое описание
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      PathoMorphology
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Stas Bykov aka Savage (savage1981@gmail.com)
* @version      28.08.2010
* @comment      Префикс для id компонентов EHMEF (EvnHistologicMicroEditForm)
*
*
* Использует: -
*/

sw.Promed.swEvnHistologicMicroEditWindow = Ext.extend(sw.Promed.BaseForm, {
	action: null,
	autoScroll: false,
	autoHeight: true,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	collapsible: false,
	doSave: function(options) {
		// options @Object

		if ( this.formStatus == 'save' || this.action == 'view' ) {
			return false;
		}

		this.formStatus = 'save';

		var base_form = this.findById('EvnHistologicMicroEditForm').getForm();

		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					this.formStatus = 'edit';
					this.findById('EvnHistologicMicroEditForm').getFirstInvalidEl().focus(true);
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

				if ( action.result && action.result.EvnHistologicMicro_id > 0 ) {
					base_form.findField('EvnHistologicMicro_id').setValue(action.result.EvnHistologicMicro_id);

					var data = new Object();
					var histologic_specimen_place_name = '';
					var record = base_form.findField('HistologicSpecimenPlace_id').getStore().getById(base_form.findField('HistologicSpecimenPlace_id').getValue());

					if ( record ) {
						histologic_specimen_place_name = record.get('HistologicSpecimenPlace_Name');
					}

					data.evnHistologicMicroData = {
						'EvnHistologicMicro_id': base_form.findField('EvnHistologicMicro_id').getValue(),
						'EvnHistologicProto_id': base_form.findField('EvnHistologicProto_id').getValue(),
						'HistologicSpecimenPlace_id': base_form.findField('HistologicSpecimenPlace_id').getValue(),
						'PrescrReactionType_id': base_form.findField('PrescrReactionType_id').getValue(),
						'PrescrReactionType_did': base_form.findField('PrescrReactionType_did').getValue(),
						'HistologicSpecimenPlace_Name': histologic_specimen_place_name,
						'EvnHistologicMicro_Count': base_form.findField('EvnHistologicMicro_Count').getValue(),
						'EvnHistologicMicro_Descr': base_form.findField('EvnHistologicMicro_Descr').getValue()
					};

					this.callback(data);

					if ( this.action == 'add' ) {
						sw.swMsg.show({
							buttons: Ext.Msg.YESNO,
							fn: function ( buttonId ) {
								this.formStatus = 'edit';

								if ( buttonId == 'yes' ) {
									var evn_histologic_proto_id = base_form.findField('EvnHistologicProto_id').getValue();

									base_form.reset();
									base_form.clearInvalid();
									base_form.findField('HistologicSpecimenPlace_id').focus();

									base_form.findField('EvnHistologicProto_id').setValue(evn_histologic_proto_id);
								}
								else {
									this.hide();
								}
							}.createDelegate(this),
							msg: lang['dobavit_esche_odno_opisanie'],
							title: lang['vopros']
						});
					}
					else {
						this.hide();
					}
				}
				else {
					sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_2]']);
				}
			}.createDelegate(this)
		});
	},
	draggable: true,
	enableEdit: function(enable) {
		var base_form = this.findById('EvnHistologicMicroEditForm').getForm();
		var form_fields = new Array(
			'HistologicSpecimenPlace_id', // Откуда взят
			'PrescrReactionType_id', // Основной метод окраски
			'PrescrReactionType_did', // Дополнительная окраска
			'EvnHistologicMicro_Count', // Количество кусочков
			'EvnHistologicMicro_Descr' // Микроскопическая картина
		);
		var i;

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
	id: 'EvnHistologicMicroEditWindow',
	initComponent: function() {
		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.doSave();
				}.createDelegate(this),
				iconCls: 'save16',
				onShiftTabAction: function () {
					if ( this.action != 'view' ) {
						this.findById('EvnHistologicMicroEditForm').getForm().findField('EvnHistologicMicro_Descr').focus(true);
					}
					else {
						this.buttons[this.buttons.length - 1].focus();
					}
				}.createDelegate(this),
				onTabAction: function () {
					this.buttons[this.buttons.length - 1].focus();
				}.createDelegate(this),
				tabIndex: TABINDEX_EHMEF + 6,
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
						this.findById('EvnHistologicMicroEditForm').getForm().findField('HistologicSpecimenPlace_id').focus(true);
					}
				}.createDelegate(this),
				tabIndex: TABINDEX_EHMEF + 7,
				text: BTN_FRMCANCEL
			}],
			items: [ new Ext.form.FormPanel({
				bodyBorder: false,
				bodyStyle: 'padding: 5px 5px 0',
				border: false,
				frame: false,
				id: 'EvnHistologicMicroEditForm',
				labelAlign: 'right',
				labelWidth: 160,
				layout: 'form',
				reader: new Ext.data.JsonReader({
					success: Ext.emptyFn
				}, [
					{ name: 'EvnHistologicMicro_id' },
					{ name: 'EvnHistologicProto_id' },
					{ name: 'HistologicSpecimenPlace_id' },
					{ name: 'PrescrReactionType_id' },
					{ name: 'PrescrReactionType_did' },
					{ name: 'EvnHistologicMicro_Count' },
					{ name: 'EvnHistologicMicro_Descr' }
				]),
				url: '/?c=EvnHistologicMicro&m=saveEvnHistologicMicro',
				items: [{
					name: 'EvnHistologicMicro_id',
					value: 0,
					xtype: 'hidden'
				}, {
					name: 'EvnHistologicProto_id',
					value: 0,
					xtype: 'hidden'
				}, {
					allowBlank: false,
					comboSubject: 'HistologicSpecimenPlace',
					fieldLabel: lang['otkuda_vzyat'],
					hiddenName: 'HistologicSpecimenPlace_id',
					listeners: {
						'keydown': function(inp, e) {
							if ( e.getKey() == Ext.EventObject.TAB && e.shiftKey == true ) {
								e.stopEvent();
								this.buttons[this.buttons.length - 1].focus();
							}
						}.createDelegate(this)
					},
					tabIndex: TABINDEX_EHMEF + 1,
					width: 250,
					xtype: 'swcommonsprcombo'
				}, {
					allowBlank: false,
					allowDecimals: false,
					allowNegative: false,
					fieldLabel: lang['kolichestvo_kusochkov'],
					name: 'EvnHistologicMicro_Count',
					tabIndex: TABINDEX_EHMEF + 2,
					width: 100,
					xtype: 'numberfield'
				}, {
					allowBlank: false,
					comboSubject: 'PrescrReactionType',
					fieldLabel: lang['osnovnoy_metod_okraski'],
					hiddenName: 'PrescrReactionType_id',
					tabIndex: TABINDEX_EHMEF + 3,
					width: 250,
					xtype: 'swcommonsprcombo'
				}, {
					allowBlank: true,
					comboSubject: 'PrescrReactionType',
					fieldLabel: lang['dopolnitelnaya_okraska'],
					hiddenName: 'PrescrReactionType_did',
					tabIndex: TABINDEX_EHMEF + 4,
					width: 250,
					xtype: 'swcommonsprcombo'
				}, {
					fieldLabel: lang['mikroskopicheskaya_kartina'],
					height: 100,
					name: 'EvnHistologicMicro_Descr',
					tabIndex: TABINDEX_EHMEF + 5,
					width: 430,
					xtype: 'textarea'
				}]
			})]
		});

		sw.Promed.swEvnHistologicMicroEditWindow.superclass.initComponent.apply(this, arguments);
	},
	keys: [{
		alt: true,
		fn: function(inp, e) {
			var current_window = Ext.getCmp('EvnHistologicMicroEditWindow');

			switch ( e.getKey() ) {
				case Ext.EventObject.C:
					current_window.doSave();
				break;

				case Ext.EventObject.J:
					current_window.hide();
				break;
			}
		}.createDelegate(this),
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
		sw.Promed.swEvnHistologicMicroEditWindow.superclass.show.apply(this, arguments);

		this.center();

		var base_form = this.findById('EvnHistologicMicroEditForm').getForm();
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
				this.setTitle(WND_PATHOMORPH_EHMEFADD);
				this.enableEdit(true);

				loadMask.hide();

				base_form.clearInvalid();

				base_form.findField('HistologicSpecimenPlace_id').focus(true, 250);
			break;

			case 'edit':
			case 'view':
				if ( this.action == 'edit' ) {
					this.setTitle(WND_PATHOMORPH_EHMEFEDIT);
					this.enableEdit(true);
				}
				else {
					this.setTitle(WND_PATHOMORPH_EHMEFVIEW);
					this.enableEdit(false);
				}

				loadMask.hide();

				base_form.clearInvalid();

				if ( this.action == 'edit' ) {
					base_form.findField('HistologicSpecimenPlace_id').focus(true, 250);
				}
				else {
					this.buttons[this.buttons.length - 1].focus();
				}
			break;

			default:
				loadMask.hide();
				this.hide();
			break;
		}
	},
	width: 650
});
