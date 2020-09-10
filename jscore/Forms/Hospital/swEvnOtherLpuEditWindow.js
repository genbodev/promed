/**
* swEvnOtherLpuEditWindow - окно редактирования/добавления диагноза в стационаре.
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
* @comment      Префикс для id компонентов EOLEF (EvnOtherLpuEditForm)
*
*
* @input data: action - действие (add, edit)
*              Person_id - ID человека
*              PersonEvn_id - ID состояния человека
*              Server_id - ID сервера
*/
/*NO PARSE JSON*/

sw.Promed.swEvnOtherLpuEditWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	objectName: 'swEvnOtherLpuEditWindow',
	objectSrc: '/jscore/Forms/Hospital/swEvnOtherLpuEditWindow.js',

	action: null,
	autoHeight: true,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	doSave: function() {
		if ( this.formStatus == 'save' ) {
			return false;
		}

		this.formStatus = 'save';

		var form = this.findById('EvnOtherLpuEditForm');
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
		
		params.EvnOtherLpu_setDate = Ext.util.Format.date(base_form.findField('EvnOtherLpu_setDate').getValue(), 'd.m.Y');

		if ( base_form.findField('EvnOtherLpu_setTime').disabled ) {
			params.EvnOtherLpu_setTime = base_form.findField('EvnOtherLpu_setTime').getRawValue();
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

				if ( action.result && action.result.EvnOtherLpu_id > 0 ) {
					base_form.findField('EvnOtherLpu_id').setValue(action.result.EvnOtherLpu_id);

					var data = new Object();

					data.evnData = {
						 'id': base_form.findField('EvnOtherLpu_id').getValue()
						,'setDate': base_form.findField('EvnOtherLpu_setDate').getValue()
						,'setTime': base_form.findField('EvnOtherLpu_setTime').getValue()
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
	id: 'EvnOtherLpuEditWindow',
	initComponent: function() {
		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.doSave();
				}.createDelegate(this),
				iconCls: 'save16',
				tabIndex: TABINDEX_EOLEF + 7,
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
					this.findById('EvnOtherLpuEditForm').getForm().findField('EvnOtherLpu_setDate').focus(true);
				}.createDelegate(this),
				tabIndex: TABINDEX_EOLEF + 8,
				text: BTN_FRMCANCEL
			}],
			items: [ new sw.Promed.PersonInformationPanelShort({
				id: 'EOLEF_PersonInformationFrame'
			}),
			new Ext.form.FormPanel({
				bodyBorder: false,
				bodyStyle: 'padding: 5px 5px 0',
				border: false,
				buttonAlign: 'left',
				frame: false,
				id: 'EvnOtherLpuEditForm',
				labelAlign: 'right',
				labelWidth: 170,
				items: [{
					name: 'EvnOtherLpu_id',
					value: 0,
					xtype: 'hidden'
				}, {
					name: 'EvnOtherLpu_pid',
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
									blockedDateAfterPersonDeath('personpanelid', 'EOLEF_PersonInformationFrame', field, newValue, oldValue);
								},
								'keydown': function(inp, e) {
									if ( e.getKey() == Ext.EventObject.TAB && e.shiftKey == true ) {
										e.stopEvent();
										this.buttons[this.buttons.length - 1].focus();
									}
								}.createDelegate(this)
							},
							name: 'EvnOtherLpu_setDate',
							plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
							selectOnFocus: true,
							tabIndex: TABINDEX_EOLEF + 1,
							width: 100,
							xtype: 'swdatefield'
						}]
					}, {
						border: false,
						labelWidth: 50,
						layout: 'form',
						items: [{
							fieldLabel: lang['vremya'],
							name: 'EvnOtherLpu_setTime',
							listeners: {
								'keydown': function (inp, e) {
									if ( e.getKey() == Ext.EventObject.F4 ) {
										e.stopEvent();
										inp.onTriggerClick();
									}
								}
							},
							onTriggerClick: function() {
								var base_form = this.findById('EvnOtherLpuEditForm').getForm();
								var time_field = base_form.findField('EvnOtherLpu_setTime');

								if ( time_field.disabled ) {
									return false;
								}

								setCurrentDateTime({
									dateField: base_form.findField('EvnOtherLpu_setDate'),
									loadMask: true,
									setDate: true,
									setDateMaxValue: false,
									setDateMinValue: false,
									setTime: true,
									timeField: time_field,
									windowId: 'EvnOtherLpuEditWindow'
								});
							}.createDelegate(this),
							plugins: [ new Ext.ux.InputTextMask('99:99', true) ],
							tabIndex: TABINDEX_EOLEF + 2,
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
					name: 'EvnOtherLpu_UKL',
					tabIndex: TABINDEX_EOLEF + 3,
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
					tabIndex: TABINDEX_EOLEF + 4,
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
					tabIndex: TABINDEX_EOLEF + 5,
					width: 430,
					xtype: 'swcommonsprcombo'
				}, {
					allowBlank: false,
					displayField: 'Org_Name',
					editable: false,
					enableKeyEvents: true,
					fieldLabel: lang['lpu'],
					hiddenName: 'Org_oid',
					listeners: {
						'keydown': function( inp, e ) {
							if ( inp.disabled )
								return;

							if ( e.F4 == e.getKey() ) {
								if ( e.browserEvent.stopPropagation )
									e.browserEvent.stopPropagation();
								else
									e.browserEvent.cancelBubble = true;

								if ( e.browserEvent.preventDefault )
									e.browserEvent.preventDefault();
								else
									e.browserEvent.returnValue = false;

								e.returnValue = false;

								if ( Ext.isIE ) {
									e.browserEvent.keyCode = 0;
									e.browserEvent.which = 0;
								}

								inp.onTrigger1Click();
								return false;
							}
						},
						'keyup': function(inp, e) {
							if ( e.F4 == e.getKey() ) {
								if ( e.browserEvent.stopPropagation )
									e.browserEvent.stopPropagation();
								else
									e.browserEvent.cancelBubble = true;

								if ( e.browserEvent.preventDefault )
									e.browserEvent.preventDefault();
								else
									e.browserEvent.returnValue = false;

								e.returnValue = false;

								if ( Ext.isIE ) {
									e.browserEvent.keyCode = 0;
									e.browserEvent.which = 0;
								}

								return false;
							}
						}
					},
					mode: 'local',
					onTrigger1Click: function() {
						var base_form = this.findById('EvnOtherLpuEditForm').getForm();
						var combo = base_form.findField('Org_oid');

						if ( combo.disabled ) {
							return false;
						}

						getWnd('swOrgSearchWindow').show({
							OrgType_id: 11,
							onClose: function() {
								combo.focus(true, 200)
							},
							onSelect: function(org_data) {
								if ( org_data.Org_id > 0 ) {
									combo.getStore().loadData([{
										Org_id: org_data.Org_id,
										Org_Name: org_data.Org_Name
									}]);
									combo.setValue(org_data.Org_id);
									getWnd('swOrgSearchWindow').hide();
									combo.collapse();
								}
							}
						});
					}.createDelegate(this),
					store: new Ext.data.JsonStore({
						autoLoad: false,
						fields: [
							{ name: 'Org_id', type: 'int' },
							{ name: 'Org_Name', type: 'string' }
						],
						key: 'Org_id',
						sortInfo: {
							field: 'Org_Name'
						},
						url: C_ORG_LIST
					}),
					tabIndex: TABINDEX_EOLEF + 6,
					tpl: new Ext.XTemplate(
						'<tpl for="."><div class="x-combo-list-item">',
						'{Org_Name}',
						'</div></tpl>'
					),
					trigger1Class: 'x-form-search-trigger',
					triggerAction: 'none',
					valueField: 'Org_id',
					width: 430,
					xtype: 'swbaseremotecombo'
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
					{ name: 'EvnOtherLpu_id' },
					{ name: 'EvnOtherLpu_pid' },
					{ name: 'EvnOtherLpu_setDate' },
					{ name: 'EvnOtherLpu_setTime' },
					{ name: 'EvnOtherLpu_UKL' },
					{ name: 'LeaveCause_id' },
					{ name: 'Org_oid' },
					{ name: 'Person_id' },
					{ name: 'PersonEvn_id' },
					{ name: 'ResultDesease_id' },
					{ name: 'Server_id' }
				]),
				url: '/?c=EvnOtherLpu&m=saveEvnOtherLpu'
			})]
		});
		sw.Promed.swEvnOtherLpuEditWindow.superclass.initComponent.apply(this, arguments);
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
		sw.Promed.swEvnOtherLpuEditWindow.superclass.show.apply(this, arguments);

		var base_form = this.findById('EvnOtherLpuEditForm').getForm();

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

		this.findById('EOLEF_PersonInformationFrame').load({
			Person_id: (arguments[0].Person_id ? arguments[0].Person_id : ''),
			Person_Birthday: (arguments[0].Person_Birthday ? arguments[0].Person_Birthday : ''),
			Person_Firname: (arguments[0].Person_Firname ? arguments[0].Person_Firname : ''),
			Person_Secname: (arguments[0].Person_Secname ? arguments[0].Person_Secname : ''),
			Person_Surname: (arguments[0].Person_Surname ? arguments[0].Person_Surname : ''),
			callback: function() {
				var field = base_form.findField('EvnOtherLpu_setDate');
				clearDateAfterPersonDeath('personpanelid', 'EOLEF_PersonInformationFrame', field);
			}			 
		});

		base_form.setValues(arguments[0].formParams);

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT });
		loadMask.show();

		if ( this.setDate ) {
			base_form.findField('EvnOtherLpu_setDate').setMaxValue(this.setDate);
			base_form.findField('EvnOtherLpu_setDate').setMinValue(this.setDate);
		}
		else {
			base_form.findField('EvnOtherLpu_setDate').setMaxValue(undefined);
			base_form.findField('EvnOtherLpu_setDate').setMinValue(undefined);
		}

		switch ( this.action ) {
			case 'add':
				this.setTitle(WND_HOSP_EVNOTHERLPUADD);

				if ( !base_form.findField('LeaveCause_id').getStore().getById(base_form.findField('LeaveCause_id').getValue()) ) {
					base_form.findField('LeaveCause_id').clearValue();
				}

				base_form.clearInvalid();

				loadMask.hide();

				base_form.findField('EvnOtherLpu_setDate').focus(false, 250);
			break;

			case 'edit':
				this.setTitle(WND_HOSP_EVNOTHERLPUEDIT);

				base_form.load({
					failure: function() {
						loadMask.hide();
						sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_zagruzke_dannyih_formyi'], function() { this.hide(); }.createDelegate(this) );
					}.createDelegate(this),
					params: {
						EvnOtherLpu_id: base_form.findField('EvnOtherLpu_id').getValue()
					},
					success: function() {
						var Org_oid = base_form.findField('Org_oid').getValue();

						base_form.findField('Org_oid').getStore().load({
							callback: function(records, options, success) {
								if ( success ) {
									base_form.findField('Org_oid').setValue(Org_oid);
								}
							},
							params: {
								Org_id: Org_oid
							}
						});

						base_form.clearInvalid();

						loadMask.hide();

						base_form.findField('EvnOtherLpu_setDate').focus(false, 250);
					},
					url: '/?c=EvnOtherLpu&m=loadEvnOtherLpuEditForm'
				});
			break;
		}
	},
	width: 650
});