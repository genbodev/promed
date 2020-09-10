/**
* swEvnSectionNarrowBedEditWindow - окно редактирования/добавления узких коек.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Hospital
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Stas Bykov aka Savage (savage@swan.perm.ru)
* @version      0.001-11.05.2010
* @comment      Префикс для id компонентов ESecNBEF (EvnSectionNarrowBedEditForm)
*
*
* @input data: action - действие (add, edit, view)
*              EvnSectionNarrowBed_id - ID случая движения для редактирования или просмотра
*              EvnSectionNarrowBed_pid - ID родительского события
*/
/*NO PARSE JSON*/

sw.Promed.swEvnSectionNarrowBedEditWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	objectName: 'swEvnSectionNarrowBedEditWindow',
	objectSrc: '/jscore/Forms/Hospital/swEvnSectionNarrowBedEditWindow.js',

	action: null,
	autoHeight: true,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: false,
	closeAction: 'hide',
	collapsible: false,
	doSave: function() {
		if ( this.formStatus == 'save' || this.action == 'view' ) {
			return false;
		}

		this.formStatus = 'save';

		var base_form = this.findById('EvnSectionNarrowBedEditForm').getForm();

		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					this.formStatus = 'edit';
					this.findById('EvnSectionNarrowBedEditForm').getFirstInvalidEl().focus(false);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		var params = new Object();

		params.EvnSectionNarrowBed_disDate = Ext.util.Format.date(base_form.findField('EvnSectionNarrowBed_disDate').getValue(), 'd.m.Y');
		params.EvnSectionNarrowBed_setDate = Ext.util.Format.date(base_form.findField('EvnSectionNarrowBed_setDate').getValue(), 'd.m.Y');

		var dis_dt = getValidDT(params.EvnSectionNarrowBed_disDate, base_form.findField('EvnSectionNarrowBed_disTime').getValue());
		var set_dt = getValidDT(params.EvnSectionNarrowBed_setDate, base_form.findField('EvnSectionNarrowBed_setTime').getValue());

		if ( dis_dt != null && set_dt > dis_dt ) {
			this.formStatus = 'edit';
			sw.swMsg.alert(lang['oshibka'], lang['data_vremya_vyipiski_menshe_datyi_vremeni_postupleniya']);
			return false;
		}

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Подождите, идет сохранение..." });
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
						sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_1]']);
					}
				}
			}.createDelegate(this),
			params: params,
			success: function(result_form, action) {
				this.formStatus = 'edit';
				loadMask.hide();

				if ( action.result ) {
					if ( action.result.EvnSectionNarrowBed_id ) {
						var evn_section_narrow_bed_id = action.result.EvnSectionNarrowBed_id;

						base_form.findField('EvnSectionNarrowBed_id').setValue(evn_section_narrow_bed_id);

						var response = new Object();

						response.accessType = 'edit';
						response.EvnSectionNarrowBed_disDate = base_form.findField('EvnSectionNarrowBed_disDate').getValue();
						response.EvnSectionNarrowBed_disTime = base_form.findField('EvnSectionNarrowBed_disTime').getValue();
						response.EvnSectionNarrowBed_id = evn_section_narrow_bed_id;
						response.EvnSectionNarrowBed_pid = base_form.findField('EvnSectionNarrowBed_pid').getValue();
						response.EvnSectionNarrowBed_setDate = base_form.findField('EvnSectionNarrowBed_setDate').getValue();
						response.EvnSectionNarrowBed_setTime = base_form.findField('EvnSectionNarrowBed_setTime').getValue();
						response.LpuSection_id = base_form.findField('LpuSection_id').getValue();
						response.PersonEvn_id = base_form.findField('PersonEvn_id').getValue();
						response.Server_id = base_form.findField('Server_id').getValue();

						var record = base_form.findField('LpuSection_id').getStore().getById(response.LpuSection_id);
						if ( record ) {
							response.LpuSectionProfile_Name = record.get('LpuSectionProfile_Name');
						}

						this.callback({ evnSectionNarrowBedData: [ response ]});
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
	},
	draggable: true,
	enableEdit: function(enable) {
		var base_form = this.findById('EvnSectionNarrowBedEditForm').getForm();
		var form_fields = new Array(
			'EvnSectionNarrowBed_disDate',
			'EvnSectionNarrowBed_disTime',
			'EvnSectionNarrowBed_setDate',
			'EvnSectionNarrowBed_setTime',
			'LpuSection_id'
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
	id: 'EvnSectionNarrowBedEditWindow',
	initComponent: function() {
		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.doSave();
				}.createDelegate(this),
				iconCls: 'save16',
				tabIndex: TABINDEX_ESECNBEF + 6,
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
				onTabAction: function() {
					if ( !this.findById('EvnSectionNarrowBedEditForm').getForm().findField('EvnSectionNarrowBed_setDate').disabled ) {
						this.findById('EvnSectionNarrowBedEditForm').getForm().findField('EvnSectionNarrowBed_setDate').focus(true);
					}
				}.createDelegate(this),
				tabIndex: TABINDEX_ESECNBEF + 7,
				text: BTN_FRMCANCEL
			}],
			items: [ new sw.Promed.PersonInformationPanelShort({
				id: 'ESecNBEF_PersonInformationFrame'
			}),
			new Ext.form.FormPanel({
				bodyBorder: false,
				bodyStyle: 'padding: 5px 5px 0',
				border: false,
				frame: false,
				id: 'EvnSectionNarrowBedEditForm',
				labelAlign: 'right',
				labelWidth: 150,
				items: [{
					name: 'EvnSectionNarrowBed_id',
					value: 0,
					xtype: 'hidden'
				}, {
					name: 'EvnSectionNarrowBed_pid',
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
							fieldLabel: lang['data_postupleniya'],
							format: 'd.m.Y',
							listeners: {
								'change': function(field, newValue, oldValue) {
									if ( blockedDateAfterPersonDeath('personpanelid', 'ESecNBEF_PersonInformationFrame', field, newValue, oldValue) )
										return false;
									
									var base_form = this.findById('EvnSectionNarrowBedEditForm').getForm();

									var lpu_section_id = base_form.findField('LpuSection_id').getValue();

									base_form.findField('LpuSection_id').clearValue();

									if ( !newValue ) {
										base_form.findField('EvnSectionNarrowBed_disDate').setMinValue(this.minDate);

										setLpuSectionGlobalStoreFilter({
											// pid: this.lpuSectionPid,
											allowLowLevel: 'yes',
											isStac: true
										});
										base_form.findField('LpuSection_id').getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));
									}
									else {
										base_form.findField('EvnSectionNarrowBed_disDate').setMinValue(newValue);

										setLpuSectionGlobalStoreFilter({
											// pid: this.lpuSectionPid,
											onDate: Ext.util.Format.date(newValue, 'd.m.Y'),
											allowLowLevel: 'yes',
											isStac: true
										});
										base_form.findField('LpuSection_id').getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));
									}

									if ( base_form.findField('LpuSection_id').getStore().getById(lpu_section_id) ) {
										base_form.findField('LpuSection_id').setValue(lpu_section_id);
									}
								}.createDelegate(this),
								'keydown': function(inp, e) {
									if ( e.getKey() == Ext.EventObject.TAB && e.shiftKey == true ) {
										e.stopEvent();
										this.buttons[this.buttons.length - 1].focus();
									}
								}.createDelegate(this)
							},
							name: 'EvnSectionNarrowBed_setDate',
							plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
							selectOnFocus: true,
							tabIndex: TABINDEX_ESECNBEF + 1,
							width: 100,
							xtype: 'swdatefield'
						}]
					}, {
						border: false,
						labelWidth: 50,
						layout: 'form',
						items: [{
							allowBlank: false,
							fieldLabel: lang['vremya'],
							listeners: {
								'change': function(field, newValue, oldValue) {
									var base_form = this.findById('EvnSectionNarrowBedEditForm').getForm();
									base_form.findField('EvnSectionNarrowBed_setDate').fireEvent('change', base_form.findField('EvnSectionNarrowBed_setDate'), base_form.findField('EvnSectionNarrowBed_setDate').getValue());
								}.createDelegate(this),
								'keydown': function (inp, e) {
									if ( e.getKey() == Ext.EventObject.F4 ) {
										e.stopEvent();
										inp.onTriggerClick();
									}
								}
							},
							name: 'EvnSectionNarrowBed_setTime',
							onTriggerClick: function() {
								var base_form = this.findById('EvnSectionNarrowBedEditForm').getForm();
								var time_field = base_form.findField('EvnSectionNarrowBed_setTime');

								if ( time_field.disabled ) {
									return false;
								}

								setCurrentDateTime({
									callback: function() {
										base_form.findField('EvnSectionNarrowBed_disDate').setMinValue(base_form.findField('EvnSectionNarrowBed_setDate').getValue());
										base_form.findField('EvnSectionNarrowBed_setDate').fireEvent('change', base_form.findField('EvnSectionNarrowBed_setDate'), base_form.findField('EvnSectionNarrowBed_setDate').getValue());
									}.createDelegate(this),
									dateField: base_form.findField('EvnSectionNarrowBed_setDate'),
									loadMask: true,
									setDate: true,
									setDateMaxValue: false,
									setDateMinValue: false,
									setTime: true,
									timeField: time_field,
									windowId: 'EvnSectionNarrowBedEditWindow'
								});
							}.createDelegate(this),
							plugins: [ new Ext.ux.InputTextMask('99:99', true) ],
							tabIndex: TABINDEX_ESECNBEF + 2,
							validateOnBlur: false,
							width: 60,
							xtype: 'swtimefield'
						}]
					}]
				}, {
					border: false,
					layout: 'column',
					items: [{
						border: false,
						layout: 'form',
						items: [{
							fieldLabel: lang['data_vyipiski'],
							format: 'd.m.Y',
							name: 'EvnSectionNarrowBed_disDate',
							plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
							selectOnFocus: true,
							tabIndex: TABINDEX_ESECNBEF + 3,
							width: 100,
							xtype: 'swdatefield'
						}]
					}, {
						border: false,
						labelWidth: 50,
						layout: 'form',
						items: [{
							border: false,
							labelWidth: 50,
							layout: 'form',
							items: [{
								fieldLabel: lang['vremya'],
								listeners: {
									'keydown': function (inp, e) {
										if ( e.getKey() == Ext.EventObject.F4 ) {
											e.stopEvent();
											inp.onTriggerClick();
										}
									}
								},
								name: 'EvnSectionNarrowBed_disTime',
								onTriggerClick: function() {
									var base_form = this.findById('EvnSectionNarrowBedEditForm').getForm();
									var time_field = base_form.findField('EvnSectionNarrowBed_disTime');

									if ( time_field.disabled ) {
										return false;
									}

									setCurrentDateTime({
										dateField: base_form.findField('EvnSectionNarrowBed_disDate'),
										loadMask: true,
										setDate: true,
										setDateMaxValue: false,
										setDateMinValue: false,
										setTime: true,
										timeField: time_field,
										windowId: 'EvnSectionNarrowBedEditWindow'
									});
								}.createDelegate(this),
								plugins: [ new Ext.ux.InputTextMask('99:99', true) ],
								tabIndex: TABINDEX_ESECNBEF + 4,
								validateOnBlur: false,
								width: 60,
								xtype: 'swtimefield'
							}]
						}]
					}]
				}, {
					allowBlank: false,
					hiddenName: 'LpuSection_id',
					tabIndex: TABINDEX_ESECNBEF + 5,
					width: 450,
					xtype: 'swlpusectionglobalcombo'
				}],
				reader: new Ext.data.JsonReader({
					success: Ext.emptyFn
				}, [
					{ name: 'EvnSectionNarrowBed_pid' },
					{ name: 'EvnSectionNarrowBed_disDate' },
					{ name: 'EvnSectionNarrowBed_disTime' },
					{ name: 'EvnSectionNarrowBed_id' },
					{ name: 'EvnSectionNarrowBed_setDate' },
					{ name: 'EvnSectionNarrowBed_setTime' },
					{ name: 'LpuSection_id' },
					{ name: 'PersonEvn_id' },
					{ name: 'Server_id' }
				]),
				region: 'center',
				url: '/?c=EvnSectionNarrowBed&m=saveEvnSectionNarrowBed'
			})]
		});
		sw.Promed.swEvnSectionNarrowBedEditWindow.superclass.initComponent.apply(this, arguments);
	},
	keys: [{
		alt: true,
		fn: function(inp, e) {
			var current_window = Ext.getCmp('EvnSectionNarrowBedEditWindow');

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
		sw.Promed.swEvnSectionNarrowBedEditWindow.superclass.show.apply(this, arguments);

		this.center();

		var base_form = this.findById('EvnSectionNarrowBedEditForm').getForm();
		base_form.reset();

		this.action = null;
		this.callback = Ext.emptyFn;
		this.formStatus = 'edit';
		this.lpuSectionPid = null;
		this.maxDate = null;
		this.minDate = null;
		this.onHide = Ext.emptyFn;

		if ( !arguments[0] || !arguments[0].formParams /*|| !arguments[0].LpuSection_pid*/ ) {
			sw.swMsg.alert(lang['soobschenie'], lang['nevernyie_parametryi']);
			return false;
		}

		if ( arguments[0].action ) {
			this.action = arguments[0].action;
		}

		if ( arguments[0].callback ) {
			this.callback = arguments[0].callback;
		}

		if ( arguments[0].LpuSection_pid ) {
			this.lpuSectionPid = arguments[0].LpuSection_pid;
		}

		if ( arguments[0].maxDate ) {
			this.maxDate = arguments[0].maxDate;
		}

		if ( arguments[0].minDate ) {
			this.minDate = arguments[0].minDate;
		}

		if ( arguments[0].onHide ) {
			this.onHide = arguments[0].onHide;
		}

		this.findById('ESecNBEF_PersonInformationFrame').load({
			Person_id: (arguments[0].Person_id ? arguments[0].Person_id : ''),
			Person_Birthday: (arguments[0].Person_Birthday ? arguments[0].Person_Birthday : ''),
			Person_Firname: (arguments[0].Person_Firname ? arguments[0].Person_Firname : ''),
			Person_Secname: (arguments[0].Person_Secname ? arguments[0].Person_Secname : ''),
			Person_Surname: (arguments[0].Person_Surname ? arguments[0].Person_Surname : ''),
			callback: function() {
				var field = base_form.findField('EvnSectionNarrowBed_setDate');
				clearDateAfterPersonDeath('personpanelid', 'ESecNBEF_PersonInformationFrame', field);
			}
		});

		base_form.setValues(arguments[0].formParams);
		base_form.findField('EvnSectionNarrowBed_setDate').fireEvent('change', base_form.findField('EvnSectionNarrowBed_setDate'), base_form.findField('EvnSectionNarrowBed_setDate').getValue());
		base_form.findField('EvnSectionNarrowBed_disDate').fireEvent('change', base_form.findField('EvnSectionNarrowBed_disDate'), base_form.findField('EvnSectionNarrowBed_disDate').getValue());
		base_form.findField('LpuSection_id').fireEvent('change', base_form.findField('LpuSection_id'), base_form.findField('LpuSection_id').getValue());

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT });
		loadMask.show();

		base_form.findField('EvnSectionNarrowBed_disDate').setMaxValue(this.maxDate);
		base_form.findField('EvnSectionNarrowBed_setDate').setMaxValue(this.maxDate);
		base_form.findField('EvnSectionNarrowBed_disDate').setMinValue(this.minDate);
		base_form.findField('EvnSectionNarrowBed_setDate').setMinValue(this.minDate);

		switch ( this.action ) {
			case 'add':
				this.setTitle(WND_HOSP_ESECNBADD);
				this.enableEdit(true);

				if ( !base_form.findField('EvnSectionNarrowBed_setDate').disabled ) {
					base_form.findField('EvnSectionNarrowBed_setDate').focus(true, 200);
				}
				else {
					this.buttons[this.buttons.length - 1].focus();
				}
			break;

			case 'edit':
				this.setTitle(WND_HOSP_ESECNBEDIT);
				this.enableEdit(true);

				if ( !base_form.findField('EvnSectionNarrowBed_setDate').disabled ) {
					base_form.findField('EvnSectionNarrowBed_setDate').focus(true, 200);
				}
				else {
					this.buttons[this.buttons.length - 1].focus();
				}
			break;

			case 'view':
				this.setTitle(WND_HOSP_ESECNBVIEW);
				this.enableEdit(false);

				this.buttons[this.buttons.length - 1].focus();
			break;

			default:
				this.hide();
			break;
		}

		loadMask.hide();
		//base_form.clearInvalid();
	},
	width: 650
});