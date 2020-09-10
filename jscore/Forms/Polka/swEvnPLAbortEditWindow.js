/**
* swEvnPLAbortEditWindow - окно редактирования/добавления сведений об аборте.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Polka
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Stas Bykov aka Savage (savage1981@gmail.com)
* @version      0.001-23.11.2009
* @comment      Префикс для id компонентов EPAbEF (EvnPLAbortEditForm)
*
*
* @input data: action - действие (add, edit, view)
*              EvnPLAction - режим открытия родительского окна (add, edit)
*/

sw.Promed.swEvnPLAbortEditWindow = Ext.extend(sw.Promed.BaseForm, {
	action: null,
	autoHeight: true,
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	draggable: true,
	doSave: function() {
		var form = this.findById('EvnPLAbortEditForm');
		var base_form = form.getForm();

		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					form.getFirstInvalidEl().focus(true);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		var data = new Object();
		var record;

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT_SAVE });
		loadMask.show();

		base_form.submit({
			failure: function(result_form, action) {
				loadMask.hide();

				if ( action.result ) {
					if ( action.result.Error_Msg ) {
						sw.swMsg.alert(lang['oshibka'], action.result.Error_Msg);
					}
					else {
						sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_3]']);
					}
				}
			},
			params: data,
			success: function(result_form, action) {
				loadMask.hide();

				if ( action.result && action.result.EvnPLAbort_id > 0 ) {
					base_form.findField('EvnPLAbort_id').setValue(action.result.EvnPLAbort_id);

					data.EvnPLAbortData = getAllFormFieldValues(form);

					this.callback(data);
					this.hide();
				}
				else {
					sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_2]']);
				}
			}.createDelegate(this)
		});
	},
	enableEdit: function(enable) {
		var base_form = this.findById('EvnPLAbortEditForm').getForm();

		if ( enable ) {
			base_form.findField('AbortPlace_id').enable();
			base_form.findField('AbortType_id').enable();
			base_form.findField('EvnPLAbort_IsHIV').enable();
			base_form.findField('EvnPLAbort_IsInf').enable();
			base_form.findField('EvnPLAbort_IsMed').enable();
			base_form.findField('EvnPLAbort_PregCount').enable();
			base_form.findField('EvnPLAbort_PregSrok').enable();
			base_form.findField('EvnPLAbort_setDate').enable();

			this.buttons[0].enable();
		}
		else {
			base_form.findField('AbortPlace_id').disable();
			base_form.findField('AbortType_id').disable();
			base_form.findField('EvnPLAbort_IsHIV').disable();
			base_form.findField('EvnPLAbort_IsInf').disable();
			base_form.findField('EvnPLAbort_IsMed').disable();
			base_form.findField('EvnPLAbort_PregCount').disable();
			base_form.findField('EvnPLAbort_PregSrok').disable();
			base_form.findField('EvnPLAbort_setDate').disable();

			this.buttons[0].disable();
		}
	},
	id: 'EvnPLAbortEditWindow',
	initComponent: function() {
		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.doSave();
				}.createDelegate(this),
				iconCls: 'save16',
				tabIndex: TABINDEX_EPLABORTEF + 8,
				text: BTN_FRMSAVE
			}, {
				text: '-'
			},
			HelpButton(this, TABINDEX_EPLABORTEF + 9),
			{
				handler: function() {
					this.hide();
				}.createDelegate(this),
				iconCls: 'cancel16',
				tabIndex: TABINDEX_EPLABORTEF + 10,
				text: BTN_FRMCANCEL
			}],
			items: [ new sw.Promed.PersonInformationPanelShort({
				id: 'EPAbEF_PersonInformationFrame'
			}),
			new Ext.form.FormPanel({
				bodyBorder: false,
				bodyStyle: 'padding: 5px 5px 0',
				border: false,
				frame: false,
				id: 'EvnPLAbortEditForm',
				labelAlign: 'right',
				labelWidth: 150,
				items: [{
					name: 'EvnPLAbort_id',
					value: 0,
					xtype: 'hidden'
				}, {
					name: 'EvnPL_id',
					value: 0,
					xtype: 'hidden'
				}, {
					name: 'Person_id',
					value: 0,
					xtype: 'hidden'
				}, {
					name: 'PersonEvn_id',
					value: 0,
					xtype: 'hidden'
				}, {
					name: 'Server_id',
					value: -1,
					xtype: 'hidden'
				}, {
					allowBlank: false,
					fieldLabel: lang['data_aborta'],
					format: 'd.m.Y',
					listeners: {
						'change': function(field, newValue, oldValue) {
							blockedDateAfterPersonDeath('personpanelid', 'EPAbEF_PersonInformationFrame', field, newValue, oldValue);
						},
						'keydown': function (inp, e) {
							if ( e.shiftKey == false && e.getKey() == Ext.EventObject.TAB ) {
								e.stopEvent();
								this.findById('EvnPLAbortEditForm').getForm().findField('AbortType_id').focus(250, true);
							}
						}.createDelegate(this)
					},
					name: 'EvnPLAbort_setDate',
					plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
					tabIndex: TABINDEX_EPLABORTEF + 11,
					width: 100,
					xtype: 'swdatefield'
				}, {
					allowBlank: false,
					hiddenName: 'AbortType_id',
					listeners: {
						'keydown': function (inp, e) {
							if ( e.shiftKey == true && e.getKey() == Ext.EventObject.TAB ) {
								e.stopEvent();
								this.findById('EvnPLAbortEditForm').getForm().findField('EvnPLAbort_setDate').focus(250, true);
							}
						}.createDelegate(this)
					},
					tabIndex: TABINDEX_EPLABORTEF + 1,
					width: 300,
					xtype: 'swaborttypecombo'
				}, {
					allowBlank: false,
					allowDecimals: false,
					allowNegative: false,
					fieldLabel: lang['srok_beremennosti'],
					maxValue: 28,
					minValue: 0,
					name: 'EvnPLAbort_PregSrok',
					tabIndex: TABINDEX_EPLABORTEF + 2,
					width: 100,
					xtype: 'numberfield'
				}, {
					allowBlank: false,
					allowDecimals: false,
					allowNegative: false,
					fieldLabel: lang['kotoraya_beremennost'],
					maxValue: 99,
					minValue: 1,
					name: 'EvnPLAbort_PregCount',
					tabIndex: TABINDEX_EPLABORTEF + 3,
					width: 100,
					xtype: 'numberfield'
				}, {
					allowBlank: false,
					hiddenName: 'AbortPlace_id',
					tabIndex: TABINDEX_EPLABORTEF + 4,
					width: 100,
					xtype: 'swabortplacecombo'
				}, {
					allowBlank: false,
					fieldLabel: lang['medikamentoznyiy'],
					hiddenName: 'EvnPLAbort_IsMed',
					tabIndex: TABINDEX_EPLABORTEF + 5,
					width: 100,
					xtype: 'swyesnocombo'
				}, {
					allowBlank: false,
					fieldLabel: lang['obsledovana_na_vich'],
					hiddenName: 'EvnPLAbort_IsHIV',
					tabIndex: TABINDEX_EPLABORTEF + 6,
					width: 100,
					xtype: 'swyesnocombo'
				}, {
					allowBlank: false,
					fieldLabel: lang['nalichie_vich-infektsii'],
					hiddenName: 'EvnPLAbort_IsInf',
					tabIndex: TABINDEX_EPLABORTEF + 7,
					width: 100,
					xtype: 'swyesnocombo'
				}],
				keys: [{
					alt: true,
					fn: function(inp, e) {
						switch ( e.getKey() ) {
							case Ext.EventObject.C:
								if ( this.action != 'view' ) {
									this.doSave();
								}
							break;

							case Ext.EventObject.J:
								this.hide();
							break;
						}
					},
					key: [ Ext.EventObject.C, Ext.EventObject.J ],
					scope: this,
					stopEvent: true
				}],
				layout: 'form',
				reader: new Ext.data.JsonReader({
					success: Ext.emptyFn
				}, [
					{ name: 'EvnPLAbort_id' }
				]),
				url: '/?c=EvnPL&m=saveEvnPLAbort'
			})]
		});
		sw.Promed.swEvnPLAbortEditWindow.superclass.initComponent.apply(this, arguments);
	},
	keys: [{
		alt: true,
		fn: function(inp, e) {
			e.stopEvent();

			if ( e.browserEvent.stopPropagation ) {
				e.browserEvent.stopPropagation();
			}
			else {
				e.browserEvent.cancelBubble = true;
			}

			if ( e.browserEvent.preventDefault ) {
				e.browserEvent.preventDefault();
			}
			else {
				e.browserEvent.returnValue = false;
			}

			e.returnValue = false;

			if ( Ext.isIE ) {
				e.browserEvent.keyCode = 0;
				e.browserEvent.which = 0;
			}

			var current_window = Ext.getCmp('EvnPLAbortEditWindow');

			if ( e.getKey() == Ext.EventObject.J ) {
				current_window.hide();
			}
			else if ( e.getKey() == Ext.EventObject.C ) {
				if ( 'view' != current_window.action ) {
					current_window.doSave();
				}
			}
		},
		key: [ Ext.EventObject.C, Ext.EventObject.J ],
		scope: this,
		stopEvent: false
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
		sw.Promed.swEvnPLAbortEditWindow.superclass.show.apply(this, arguments);

		var base_form = this.findById('EvnPLAbortEditForm').getForm();

		this.center();
		base_form.reset();

		this.action = null;
		this.callback = Ext.emptyFn;
		this.onHide = Ext.emptyFn;

		if ( !arguments[0] || !arguments[0].formParams ) {
			sw.swMsg.alert(lang['soobschenie'], lang['nevernyie_parametryi']);
			return false;
		}

		if ( arguments[0].action ) {
			this.action = arguments[0].action;
		}

		if ( arguments[0].callback ) {
			this.callback = arguments[0].callback;
		}

		if ( arguments[0].onHide ) {
			this.onHide = arguments[0].onHide;
		}

		this.findById('EPAbEF_PersonInformationFrame').load({
			Person_id: (arguments[0].Person_id ? arguments[0].Person_id : ''),
			Person_Birthday: (arguments[0].Person_Birthday ? arguments[0].Person_Birthday : ''),
			Person_Firname: (arguments[0].Person_Firname ? arguments[0].Person_Firname : ''),
			Person_Secname: (arguments[0].Person_Secname ? arguments[0].Person_Secname : ''),
			Person_Surname: (arguments[0].Person_Surname ? arguments[0].Person_Surname : ''),
			callback: function() {
				var field = base_form.findField('EvnPLAbort_setDate');
				clearDateAfterPersonDeath('personpanelid', 'EPAbEF_PersonInformationFrame', field);
			}
		});

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT });
		loadMask.show();

		base_form.setValues(arguments[0].formParams);

		switch ( this.action ) {
			case 'add':
				this.setTitle(WND_POL_APLABORTADD);
				this.enableEdit(true);

				loadMask.hide();

				base_form.findField('EvnPLAbort_setDate').focus(false, 250);
			break;

			case 'edit':
				this.setTitle(WND_POL_APLABORTEDIT);
				this.enableEdit(true);

				loadMask.hide();

				base_form.findField('EvnPLAbort_setDate').focus(false, 250);
			break;

			case 'view':
				this.setTitle(WND_POL_APLABORTVIEW);
				this.enableEdit(false);

				loadMask.hide();

				this.buttons[this.buttons.length - 1].focus();
			break;
		}
	},
	width: 500
});