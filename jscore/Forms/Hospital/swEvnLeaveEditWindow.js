/**
* swEvnLeaveEditWindow - окно редактирования/добавления диагноза в стационаре.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Hospital
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Stas Bykov aka Savage (savage@swan.perm.ru)
* @version      0.001-19.03.2010
* @comment      Префикс для id компонентов ELVEF (EvnLeaveEditForm)
*
*
* @input data: action - действие (add, edit)
*              Person_id - ID человека
*              PersonEvn_id - ID состояния человека
*              Server_id - ID сервера
*/
/*NO PARSE JSON*/

sw.Promed.swEvnLeaveEditWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	objectName: 'swEvnLeaveEditWindow',
	objectSrc: '/jscore/Forms/Hospital/swEvnLeaveEditWindow.js',

	action: null,
	autoHeight: true,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	doSave: function(options) {
		// options @Object
		// options.ignoreEvnLeaveMismatch @Boolean Игнорировать несоответствие причины выписки и направления на амбулаторное лечение

		if ( this.formStatus == 'save' ) {
			return false;
		}

		if ( !options || typeof options != 'object' ) {
			options = new Object();
		}

		this.formStatus = 'save';

		var form = this.findById('EvnLeaveEditForm');
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

		/**
		 *	http://redmine.swan.perm.ru/issues/4982
		 */
		if ( !options.ignoreEvnLeaveMismatch
			&& Number(base_form.findField('EvnLeave_IsAmbul').getValue()) == 2
			&& base_form.findField('LeaveCause_id').getValue().toString().inlist([ '1', '2', '3', '4' ])
		) {
			this.formStatus = 'edit';

			sw.swMsg.show({
				buttons: Ext.Msg.YESNO,
				fn: function(buttonId, text, obj) {
					if ( buttonId == 'yes' ) {
						this.doSave({
							ignoreEvnLeaveMismatch: true
						});
					}
				}.createDelegate(this),
				icon: Ext.MessageBox.QUESTION,
				msg: lang['nesootvetstvie_prichinyi_vyipiski_i_napravleniya_na_ambulatornoe_lechenie_prodoljit'],
				title: lang['vopros']
			});

			return false;
		}

		var params = new Object();

		params.from = this.from;
		params.EvnSection_id = this.EvnSection_id;

		params.EvnLeave_setDate = Ext.util.Format.date(base_form.findField('EvnLeave_setDate').getValue(), 'd.m.Y');

		if ( base_form.findField('EvnLeave_setTime').disabled ) {
			params.EvnLeave_setTime = base_form.findField('EvnLeave_setTime').getRawValue();
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
			params: params,
			success: function(result_form, action) {
				this.formStatus = 'edit';
				loadMask.hide();

				if ( action.result && action.result.EvnLeave_id > 0 ) {
					base_form.findField('EvnLeave_id').setValue(action.result.EvnLeave_id);

					var data = new Object();

					data.evnData = {
						 'id': base_form.findField('EvnLeave_id').getValue()
						,'setDate': base_form.findField('EvnLeave_setDate').getValue()
						,'setTime': base_form.findField('EvnLeave_setTime').getValue()
					};

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
	formStatus: 'edit',
	id: 'EvnLeaveEditWindow',
	initComponent: function() {
		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.doSave();
				}.createDelegate(this),
				iconCls: 'save16',
				tabIndex: TABINDEX_ELVEF + 7,
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
					this.buttons[0].focus();
				}.createDelegate(this),
				onTabAction: function () {
					this.findById('EvnLeaveEditForm').getForm().findField('EvnLeave_setDate').focus(true);
				}.createDelegate(this),
				tabIndex: TABINDEX_ELVEF + 8,
				text: BTN_FRMCANCEL
			}],
			items: [ new sw.Promed.PersonInformationPanelShort({
				id: 'ELVEF_PersonInformationFrame'
			}),
			new Ext.form.FormPanel({
				bodyBorder: false,
				bodyStyle: 'padding: 5px 5px 0',
				border: false,
				buttonAlign: 'left',
				frame: false,
				id: 'EvnLeaveEditForm',
				labelAlign: 'right',
				labelWidth: 170,
				items: [{
					name: 'EvnLeave_id',
					value: 0,
					xtype: 'hidden'
				}, {
					name: 'EvnLeave_pid',
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
					border: false,
					layout: 'column',
					items: [{
						border: false,
						layout: 'form',
						items: [{
							allowBlank: false,
							fieldLabel: lang['data_vyipiski'],
							format: 'd.m.Y',
							listeners: {
								'keydown': function(inp, e) {
									if ( e.getKey() == Ext.EventObject.TAB && e.shiftKey == true ) {
										e.stopEvent();
										this.buttons[this.buttons.length - 1].focus();
									}
								}.createDelegate(this)
							},
							name: 'EvnLeave_setDate',
							plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
							selectOnFocus: true,
							tabIndex: TABINDEX_ELVEF + 1,
							width: 100,
							xtype: 'swdatefield',
							listeners: {
								'change': function(field, newValue, oldValue) {
									blockedDateAfterPersonDeath('personpanelid', 'ELVEF_PersonInformationFrame', field, newValue, oldValue);
								}
							}							
						}]
					}, {
						border: false,
						labelWidth: 50,
						layout: 'form',
						items: [{
							fieldLabel: lang['vremya'],
							name: 'EvnLeave_setTime',
							listeners: {
								'keydown': function (inp, e) {
									if ( e.getKey() == Ext.EventObject.F4 ) {
										e.stopEvent();
										inp.onTriggerClick();
									}
								}
							},
							onTriggerClick: function() {
								var base_form = this.findById('EvnLeaveEditForm').getForm();
								var time_field = base_form.findField('EvnLeave_setTime');

								if ( time_field.disabled ) {
									return false;
								}

								setCurrentDateTime({
									dateField: base_form.findField('EvnLeave_setDate'),
									loadMask: true,
									setDate: true,
									setDateMaxValue: false,
									setDateMinValue: false,
									setTime: true,
									timeField: time_field,
									windowId: 'EvnLeaveEditWindow'
								});
							}.createDelegate(this),
							plugins: [ new Ext.ux.InputTextMask('99:99', true) ],
							tabIndex: TABINDEX_ELVEF + 2,
							validateOnBlur: false,
							width: 60,
							xtype: 'swtimefield'
						}]
					}]
				}, {
					allowBlank: false,
					allowDecimals: true,
					allowNegative: false,
					fieldLabel: lang['uroven_kachestva_lecheniya'],
					maxValue: 1,
					minValue: 0,
					name: 'EvnLeave_UKL',
					tabIndex: TABINDEX_ELVEF + 3,
					width: 70,
					value: 1,
					xtype: 'numberfield'
				}, {
					allowBlank: false,
					hiddenName: 'ResultDesease_id',
					lastQuery: '',
					listeners: {
						'render': function(combo) {
							combo.getStore().load({
								callback: function()
								{
									combo.setValue(combo.getValue());
								}
							});
						}
					},
					tabIndex: TABINDEX_ELVEF + 4,
					width: 430,
					xtype: 'swresultdeseasecombo'
				}, {
					allowBlank: false,
					hiddenName: 'LeaveCause_id',
					listeners: {
						'render': function(combo) {
							combo.getStore().load({
								callback: function()
								{
									combo.setValue(combo.getValue());
								}
							});
						}
					},
					tabIndex: TABINDEX_ELVEF + 5,
					width: 430,
					xtype: 'swleavecausecombo'
				}, {
					allowBlank: false,
					fieldLabel: lang['napravlen_na_amb_lechenie'],
					hiddenName: 'EvnLeave_IsAmbul',
					tabIndex: TABINDEX_ELVEF + 6,
					width: 70,
					xtype: 'swyesnocombo'
				}],
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
					},
					key: [ Ext.EventObject.C, Ext.EventObject.J ],
					scope: this,
					stopEvent: true
				}],
				layout: 'form',
				reader: new Ext.data.JsonReader({
					success: function() { }
				}, [
					{ name: 'EvnLeave_id' },
					{ name: 'EvnLeave_pid' },
					{ name: 'EvnLeave_IsAmbul' },
					{ name: 'EvnLeave_setDate' },
					{ name: 'EvnLeave_setTime' },
					{ name: 'EvnLeave_UKL' },
					{ name: 'LeaveCause_id' },
					{ name: 'Person_id' },
					{ name: 'PersonEvn_id' },
					{ name: 'ResultDesease_id' },
					{ name: 'Server_id' }
				]),
				url: '/?c=EvnLeave&m=saveEvnLeave'
			})]
		});
		sw.Promed.swEvnLeaveEditWindow.superclass.initComponent.apply(this, arguments);
	},
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
		sw.Promed.swEvnLeaveEditWindow.superclass.show.apply(this, arguments);

		var base_form = this.findById('EvnLeaveEditForm').getForm();

		this.center();
		base_form.reset();

		this.action = null;
		this.callback = Ext.emptyFn;
		this.formStatus = 'edit';
		this.onHide = Ext.emptyFn;
		this.setDate = null;

		if ( !arguments[0] || !arguments[0].formParams ) {
			sw.swMsg.alert(lang['soobschenie'], lang['nevernyie_parametryi']);
			return false;
		}

		this.from = arguments[0].from || null;
		this.EvnSection_id = arguments[0].EvnSection_id || null;

		if ( arguments[0].action ) {
			this.action = arguments[0].action;
		}

		if ( arguments[0].callback ) {
			this.callback = arguments[0].callback;
		}

		if ( arguments[0].onHide ) {
			this.onHide = arguments[0].onHide;
		}

		if ( arguments[0].setDate ) {
			this.setDate = arguments[0].setDate;
		}

		this.findById('ELVEF_PersonInformationFrame').load({
			Person_id: (arguments[0].Person_id ? arguments[0].Person_id : ''),
			Person_Birthday: (arguments[0].Person_Birthday ? arguments[0].Person_Birthday : ''),
			Person_Firname: (arguments[0].Person_Firname ? arguments[0].Person_Firname : ''),
			Person_Secname: (arguments[0].Person_Secname ? arguments[0].Person_Secname : ''),
			Person_Surname: (arguments[0].Person_Surname ? arguments[0].Person_Surname : ''),
			callback: function() {
				var field = base_form.findField('EvnLeave_setDate');
				clearDateAfterPersonDeath('personpanelid', 'ELVEF_PersonInformationFrame', field);
			}			 
		});

		base_form.setValues(arguments[0].formParams);

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT });
		loadMask.show();

		if ( this.setDate ) {
			base_form.findField('EvnLeave_setDate').setMaxValue(this.setDate);
			base_form.findField('EvnLeave_setDate').setMinValue(this.setDate);
		}
		else {
			base_form.findField('EvnLeave_setDate').setMaxValue(undefined);
			base_form.findField('EvnLeave_setDate').setMinValue(undefined);
		}

		switch ( this.action ) {
			case 'add':
				this.setTitle(WND_HOSP_EVNLEAVEADD);

				base_form.clearInvalid();

				loadMask.hide();

				base_form.findField('EvnLeave_setDate').focus(false, 250);
			break;

			case 'edit':
				this.setTitle(WND_HOSP_EVNLEAVEEDIT);

				base_form.load({
					failure: function() {
						loadMask.hide();
						sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_zagruzke_dannyih_formyi'], function() { this.hide(); }.createDelegate(this) );
					}.createDelegate(this),
					params: {
						EvnLeave_id: base_form.findField('EvnLeave_id').getValue()
					},
					success: function() {
						base_form.clearInvalid();
						loadMask.hide();
						base_form.findField('EvnLeave_setDate').focus(false, 250);
					},
					url: '/?c=EvnLeave&m=loadEvnLeaveEditForm'
				});
			break;
		}
	},
	width: 650
});