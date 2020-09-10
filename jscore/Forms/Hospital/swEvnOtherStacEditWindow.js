/**
* swEvnOtherStacEditWindow - окно редактирования/добавления перевода в стационар другого типа.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Hospital
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Stas Bykov aka Savage (savage@swan.perm.ru)
* @version      0.001-26.03.2010
* @comment      Префикс для id компонентов EOStacEF (EvnOtherStacEditForm)
*
*
* @input data: action - действие (add, edit)
*              Person_id - ID человека
*              PersonEvn_id - ID состояния человека
*              Server_id - ID сервера
*/
/*NO PARSE JSON*/

sw.Promed.swEvnOtherStacEditWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	objectName: 'swEvnOtherStacEditWindow',
	objectSrc: '/jscore/Forms/Hospital/swEvnOtherStacEditWindow.js',

	action: null,
	autoHeight: true,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	currentLpuUnitTypeId: null,
	doSave: function() {
		if ( this.formStatus == 'save' ) {
			return false;
		}

		this.formStatus = 'save';

		var form = this.findById('EvnOtherStacEditForm');
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

		var params = new Object();

		params.from = this.from;
		params.EvnSection_id = this.EvnSection_id;

		params.EvnOtherStac_setDate = Ext.util.Format.date(base_form.findField('EvnOtherStac_setDate').getValue(), 'd.m.Y');

		if ( base_form.findField('EvnOtherStac_setTime').disabled ) {
			params.EvnOtherStac_setTime = base_form.findField('EvnOtherStac_setTime').getRawValue();
		}

		if ( base_form.findField('LpuUnitType_oid').disabled ) {
			params.LpuUnitType_oid = base_form.findField('LpuUnitType_oid').getValue();
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

				if ( action.result && action.result.EvnOtherStac_id > 0 ) {
					base_form.findField('EvnOtherStac_id').setValue(action.result.EvnOtherStac_id);

					var data = new Object();

					data.evnData = {
						 'id': base_form.findField('EvnOtherStac_id').getValue()
						,'setDate': base_form.findField('EvnOtherStac_setDate').getValue()
						,'setTime': base_form.findField('EvnOtherStac_setTime').getValue()
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
	id: 'EvnOtherStacEditWindow',
	initComponent: function() {
		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.doSave();
				}.createDelegate(this),
				iconCls: 'save16',
				tabIndex: TABINDEX_EOSTACEF + 8,
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
					this.findById('EvnOtherStacEditForm').getForm().findField('EvnOtherStac_setDate').focus(true);
				}.createDelegate(this),
				tabIndex: TABINDEX_EOSTACEF + 9,
				text: BTN_FRMCANCEL
			}],
			items: [ new sw.Promed.PersonInformationPanelShort({
				id: 'EOStacEF_PersonInformationFrame'
			}),
			new Ext.form.FormPanel({
				bodyBorder: false,
				bodyStyle: 'padding: 5px 5px 0',
				border: false,
				buttonAlign: 'left',
				frame: false,
				id: 'EvnOtherStacEditForm',
				labelAlign: 'right',
				labelWidth: 170,
				items: [{
					name: 'EvnOtherStac_id',
					value: 0,
					xtype: 'hidden'
				}, {
					name: 'EvnOtherStac_pid',
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
								'change': function(field, newValue, oldValue) {
									if (blockedDateAfterPersonDeath('personpanelid', 'EOStacEF_PersonInformationFrame', field, newValue, oldValue)) return;
								
									var base_form = this.findById('EvnOtherStacEditForm').getForm();

									base_form.findField('LpuUnitType_oid').fireEvent('change', base_form.findField('LpuUnitType_oid'), base_form.findField('LpuUnitType_oid').getValue());
								}.createDelegate(this),
								'keydown': function(inp, e) {
									if ( e.getKey() == Ext.EventObject.TAB && e.shiftKey == true ) {
										e.stopEvent();
										this.buttons[this.buttons.length - 1].focus();
									}
								}.createDelegate(this)
							},
							name: 'EvnOtherStac_setDate',
							plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
							selectOnFocus: true,
							tabIndex: TABINDEX_EOSTACEF + 1,
							width: 100,
							xtype: 'swdatefield'
						}]
					}, {
						border: false,
						labelWidth: 50,
						layout: 'form',
						items: [{
							fieldLabel: lang['vremya'],
							name: 'EvnOtherStac_setTime',
							listeners: {
								'keydown': function (inp, e) {
									if ( e.getKey() == Ext.EventObject.F4 ) {
										e.stopEvent();
										inp.onTriggerClick();
									}
								}
							},
							onTriggerClick: function() {
								var base_form = this.findById('EvnOtherStacEditForm').getForm();
								var time_field = base_form.findField('EvnOtherStac_setTime');

								if ( time_field.disabled ) {
									return false;
								}

								setCurrentDateTime({
									dateField: base_form.findField('EvnOtherStac_setDate'),
									loadMask: true,
									setDate: true,
									setDateMaxValue: false,
									setDateMinValue: false,
									setTime: true,
									timeField: time_field,
									windowId: 'EvnOtherStacEditWindow'
								});
							}.createDelegate(this),
							plugins: [ new Ext.ux.InputTextMask('99:99', true) ],
							tabIndex: TABINDEX_EOSTACEF + 2,
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
					name: 'EvnOtherStac_UKL',
					tabIndex: TABINDEX_EOSTACEF + 3,
					width: 70,
					value: 1,
					xtype: 'numberfield'
				}, {
					allowBlank: false,
					comboSubject: 'ResultDesease',
					fieldLabel: lang['ishod_gospitalizatsii'],
					hiddenName: 'ResultDesease_id',
					lastQuery: '',
					listeners: {
						'render': function(combo) {
							combo.getStore().load();
						}
					},
					tabIndex: TABINDEX_EOSTACEF + 4,
					width: 430,
					xtype: 'swcommonsprcombo'
				}, {
					allowBlank: false,
					comboSubject: 'LeaveCause',
					fieldLabel: lang['prichina_perevoda'],
					hiddenName: 'LeaveCause_id',
					lastQuery: '',
					listeners: {
						'render': function(combo) {
							combo.getStore().load();
						}
					},
					tabIndex: TABINDEX_EOSTACEF + 5,
					width: 430,
					xtype: 'swcommonsprcombo'
				}, {
					allowBlank: false,
					fieldLabel: lang['tip_statsionara'],
					hiddenName: 'LpuUnitType_oid',
					lastQuery: '',
					listeners: {
						'change': function(combo, newValue, oldValue) {
							var base_form = this.findById('EvnOtherStacEditForm').getForm();

							var date = base_form.findField('EvnOtherStac_setDate').getValue();
							var lpu_section_oid = base_form.findField('LpuSection_oid').getValue();
							var params = new Object();
							var record = combo.getStore().getById(newValue);

							base_form.findField('LpuSection_oid').clearValue();

							params.isStac = true;

							if ( record ) {
								params.arrayLpuUnitType = [ record.get('LpuUnitType_Code') ];
							}

							if ( date ) {
								params.onDate = Ext.util.Format.date(date, 'd.m.Y');
							}

							setLpuSectionGlobalStoreFilter(params);

							base_form.findField('LpuSection_oid').getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));

							if ( base_form.findField('LpuSection_oid').getStore().getById(lpu_section_oid) ) {
								base_form.findField('LpuSection_oid').setValue(lpu_section_oid);
							}
						}.createDelegate(this)
					},
					tabIndex: TABINDEX_EOSTACEF + 6,
					width: 430,
					xtype: 'swlpuunittypecombo'
				}, {
					allowBlank: false,
					fieldLabel: lang['otdelenie'],
					hiddenName: 'LpuSection_oid',
					listWidth: 630,
					tabIndex: TABINDEX_EOSTACEF + 7,
					width: 430,
					xtype: 'swlpusectionglobalcombo'
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
					{ name: 'EvnOtherStac_id' },
					{ name: 'EvnOtherStac_pid' },
					{ name: 'EvnOtherStac_setDate' },
					{ name: 'EvnOtherStac_setTime' },
					{ name: 'EvnOtherStac_UKL' },
					{ name: 'LeaveCause_id' },
					{ name: 'LpuSection_oid' },
					{ name: 'LpuUnitType_oid' },
					{ name: 'Person_id' },
					{ name: 'PersonEvn_id' },
					{ name: 'ResultDesease_id' },
					{ name: 'Server_id' }
				]),
				url: '/?c=EvnOtherStac&m=saveEvnOtherStac'
			})]
		});
		sw.Promed.swEvnOtherStacEditWindow.superclass.initComponent.apply(this, arguments);
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
		sw.Promed.swEvnOtherStacEditWindow.superclass.show.apply(this, arguments);

		var base_form = this.findById('EvnOtherStacEditForm').getForm();

		this.center();
		base_form.reset();

		this.action = null;
		this.callback = Ext.emptyFn;
		this.currentLpuUnitTypeId = null;
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

		if ( arguments[0].currentLpuUnitTypeId ) {
			this.currentLpuUnitTypeId = arguments[0].currentLpuUnitTypeId;
		}

		if ( arguments[0].onHide ) {
			this.onHide = arguments[0].onHide;
		}

		if ( arguments[0].setDate ) {
			this.setDate = arguments[0].setDate;
		}

		this.findById('EOStacEF_PersonInformationFrame').load({
			Person_id: (arguments[0].Person_id ? arguments[0].Person_id : ''),
			Person_Birthday: (arguments[0].Person_Birthday ? arguments[0].Person_Birthday : ''),
			Person_Firname: (arguments[0].Person_Firname ? arguments[0].Person_Firname : ''),
			Person_Secname: (arguments[0].Person_Secname ? arguments[0].Person_Secname : ''),
			Person_Surname: (arguments[0].Person_Surname ? arguments[0].Person_Surname : ''),
			callback: function() {
				var field = base_form.findField('EvnOtherStac_setDate');
				clearDateAfterPersonDeath('personpanelid', 'EOStacEF_PersonInformationFrame', field);
			}
		});

		base_form.findField('LpuUnitType_oid').getStore().removeAll();

		base_form.setValues(arguments[0].formParams);

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT });
		loadMask.show();

		if ( this.setDate ) {
			base_form.findField('EvnOtherStac_setDate').setMaxValue(this.setDate);
			base_form.findField('EvnOtherStac_setDate').setMinValue(this.setDate);
		}
		else {
			base_form.findField('EvnOtherStac_setDate').setMaxValue(undefined);
			base_form.findField('EvnOtherStac_setDate').setMinValue(undefined);
		}

		switch ( this.action ) {
			case 'add':
				this.setTitle(WND_HOSP_EVNOTHERSTACADD);

				base_form.findField('EvnOtherStac_setDate').fireEvent('change', base_form.findField('EvnOtherStac_setDate'), base_form.findField('EvnOtherStac_setDate').getValue());

				var filter = 'where LpuUnitType_Code in (2, 3, 4, 5)';

				if ( this.currentLpuUnitTypeId > 0 ) {
					filter = filter + ' and LpuUnitType_id != ' + this.currentLpuUnitTypeId;
				}

				base_form.findField('LpuUnitType_oid').enable();
				base_form.findField('LpuUnitType_oid').getStore().load({
					params: {
						where: filter
					}
				});

				if ( !base_form.findField('LeaveCause_id').getStore().getById(base_form.findField('LeaveCause_id').getValue()) ) {
					base_form.findField('LeaveCause_id').clearValue();
				}

				base_form.clearInvalid();

				loadMask.hide();

				base_form.findField('EvnOtherStac_setDate').focus(false, 250);
			break;

			case 'edit':
				this.setTitle(WND_HOSP_EVNOTHERSTACEDIT);

				base_form.load({
					failure: function() {
						loadMask.hide();
						sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_zagruzke_dannyih_formyi'], function() { this.hide(); }.createDelegate(this) );
					}.createDelegate(this),
					params: {
						EvnOtherStac_id: base_form.findField('EvnOtherStac_id').getValue()
					},
					success: function() {
						var lpu_unit_type_oid = base_form.findField('LpuUnitType_oid').getValue();
						base_form.findField('LpuUnitType_oid').disable();

						if ( lpu_unit_type_oid > 0 ) {
							base_form.findField('LpuUnitType_oid').getStore().load({
								callback: function() {
									base_form.findField('LpuUnitType_oid').setValue(lpu_unit_type_oid);
								},
								params: {
									where: 'where LpuUnitType_id = ' + lpu_unit_type_oid
								}
							});
						}

						base_form.findField('EvnOtherStac_setDate').fireEvent('change', base_form.findField('EvnOtherStac_setDate'), base_form.findField('EvnOtherStac_setDate').getValue());

						base_form.clearInvalid();

						loadMask.hide();

						base_form.findField('EvnOtherStac_setDate').focus(false, 250);
					},
					url: '/?c=EvnOtherStac&m=loadEvnOtherStacEditForm'
				});
			break;
		}
	},
	width: 650
});