/**
* swEvnUslugaComplexEditWindow - окно редактирования/добавления выполнения комплексной услуги.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Polka
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Stas Bykov aka Savage (savage1981@gmail.com)
* @version      0.001-01.03.2010
* @comment      Префикс для id компонентов EUCpxEF (EvnUslugaComplexEditForm)
*
*
* @input data: parentClass - класс родительского события
*
*
* Использует: окно поиска организации (swOrgSearchWindow)
*/

sw.Promed.swEvnUslugaComplexEditWindow = Ext.extend(sw.Promed.BaseForm, {
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

		var base_form = this.findById('EvnUslugaComplexEditForm').getForm();

		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					this.formStatus = 'edit';
					this.findById('EvnUslugaComplexEditForm').getFirstInvalidEl().focus(true, 100);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		var evn_usluga_set_time = base_form.findField('EvnUslugaComplex_setTime').getValue();
		var evn_usluga_complex_pid = base_form.findField('EvnUslugaComplex_pid').getValue();

		if ( (this.parentClass == 'EvnVizit' || this.parentClass == 'EvnPS') && !evn_usluga_complex_pid ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					this.formStatus = 'edit';
					base_form.findField('EvnUslugaComplex_pid').focus(true);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: lang['ne_vyibrano_poseschenie_otdelenie'],
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		var params = new Object();
		var med_personal_id = null;
		var med_staff_fact_id = base_form.findField('MedStaffFact_id').getValue();
		var record = null;

		record = base_form.findField('MedStaffFact_id').getStore().getById(med_staff_fact_id);
		if ( record ) {
			med_personal_id = record.get('MedPersonal_id');
		}

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT_SAVE });
		loadMask.show();

		params.MedPersonal_id = med_personal_id;

		if ( base_form.findField('EvnUslugaComplex_pid').disabled ) {
			params.EvnUslugaComplex_pid = evn_usluga_complex_pid;
		}

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

				if ( action.result ) {
					if ( action.result.Error_Msg && action.result.Error_Msg.toString().length > 0 ) {
						sw.swMsg.alert(lang['oshibka'], action.result.Error_Msg.toString(), function() { this.buttons[0].focus(); }.createDelegate(this) );
					}
					else if ( action.result.Alert_Msg && action.result.Alert_Msg.toString().length > 0 ) {
						sw.swMsg.alert(lang['preduprejdenie'], action.result.Alert_Msg.toString(), function() { this.callback(); this.hide(); }.createDelegate(this) );
					}
					else {
						this.callback();
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
	height: 450,
	id: 'EvnUslugaComplexEditWindow',
	initComponent: function() {
		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.doSave();
				}.createDelegate(this),
				iconCls: 'save16',
				onShiftTabAction: function () {
					if ( !this.findById('EUCpxEF_EvnUslugaPanel').collapsed ) {
						this.findById('EvnUslugaComplexEditForm').getForm().findField('UslugaComplex_id').focus(true);
					}
				}.createDelegate(this),
				onTabAction: function () {
					this.buttons[this.buttons.length - 1].focus();
				}.createDelegate(this),
				tabIndex: TABINDEX_EUCPXEF + 12,
				text: BTN_FRMSAVE
			}, {
				text: '-'
			},
			HelpButton(this, -1),
			{
				handler: function() {
					this.onCancelAction();
				}.createDelegate(this),
				iconCls: 'cancel16',
				onShiftTabAction: function () {
					this.buttons[0].focus();
				}.createDelegate(this),
				onTabAction: function () {
					if ( !this.findById('EUCpxEF_EvnUslugaPanel').collapsed ) {
						if ( !this.findById('EvnUslugaComplexEditForm').getForm().findField('EvnUslugaComplex_pid').disabled ) {
							this.findById('EvnUslugaComplexEditForm').getForm().findField('EvnUslugaComplex_pid').focus(true, 100);
						}
						else {
							this.findById('EvnUslugaComplexEditForm').getForm().findField('EvnUslugaComplex_setDate').focus(true, 100);
						}
					}
					else {
						this.buttons[0].focus();
					}
				}.createDelegate(this),
				tabIndex: TABINDEX_EUCPXEF + 13,
				text: BTN_FRMCANCEL
			}],
			items: [ new sw.Promed.PersonInformationPanelShort({
				id: 'EUCpxEF_PersonInformationFrame',
				region: 'north'
			}),
			new Ext.form.FormPanel({
				autoScroll: true,
				bodyBorder: false,
				bodyStyle: 'padding: 5px 5px 0',
				border: false,
				frame: false,
				id: 'EvnUslugaComplexEditForm',
				labelAlign: 'right',
				labelWidth: 130,
				layout: 'form',
				reader: new Ext.data.JsonReader({
					success: Ext.emptyFn
				}, [
					{ name: 'EvnUslugaComplex_id' },
					{ name: 'EvnUslugaComplex_Kolvo' },
					{ name: 'EvnUslugaComplex_pid' },
					{ name: 'EvnUslugaComplex_rid' },
					{ name: 'EvnUslugaComplex_setDate' },
					{ name: 'Lpu_uid' },
					{ name: 'LpuSection_uid' },
					{ name: 'MedStaffFact_id' },
					{ name: 'Org_uid' },
					{ name: 'PayType_id' },
					{ name: 'Person_id' },
					{ name: 'PersonEvn_id' },
					{ name: 'Server_id' },
					{ name: 'UslugaComplex_id' },
					{ name: 'UslugaPlace_id' }
				]),
				region: 'center',
				url: '/?c=EvnUsluga&m=saveEvnUslugaComplex',
				items: [{
					name: 'EvnUslugaComplex_id',
					value: 0,
					xtype: 'hidden'
				}, {
					name: 'EvnUslugaComplex_rid',
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
					value: 0,
					xtype: 'hidden'
				},
				new sw.Promed.Panel({
					autoHeight: true,
					// bodyStyle: 'padding: 0.5em;',
					border: true,
					collapsible: true,
					id: 'EUCpxEF_EvnUslugaPanel',
					layout: 'form',
					style: 'margin-bottom: 0.5em;',
					title: lang['1_usluga'],
					items: [{
						displayField: 'Evn_Name',
						editable: false,
						enableKeyEvents: true,
						fieldLabel: lang['poseschenie'],
						hiddenName: 'EvnUslugaComplex_pid',
						listeners: {
							'change': function(combo, newValue, oldValue) {
								var base_form = this.findById('EvnUslugaComplexEditForm').getForm();
								var record = combo.getStore().getById(newValue);

								if ( record ) {
									base_form.findField('EvnUslugaComplex_setDate').setValue(record.get('Evn_setDate'));
									base_form.findField('EvnUslugaComplex_setDate').fireEvent('change', base_form.findField('EvnUslugaComplex_setDate'), record.get('Evn_setDate'), 0);
									base_form.findField('UslugaPlace_id').setValue(1);
									base_form.findField('UslugaPlace_id').fireEvent('change', base_form.findField('UslugaPlace_id'), 1, 0);

									if ( base_form.findField('LpuSection_uid').getStore().getById(record.get('LpuSection_id')) ) {
										base_form.findField('LpuSection_uid').setValue(record.get('LpuSection_id'));
									}

									var index = base_form.findField('MedStaffFact_id').getStore().findBy(function(rec) {
										return (rec.get('MedStaffFact_id') == record.get('MedStaffFact_id'));
									});

									if ( index >= 0 ) {
										base_form.findField('MedStaffFact_id').setValue(record.get('MedStaffFact_id'));
									}
								}
							}.createDelegate(this),
							'keydown': function (inp, e) {
								if ( e.shiftKey == true && e.getKey() == Ext.EventObject.TAB ) {
									e.stopEvent();
									this.buttons[this.buttons.length - 1].focus();
								}
								else if ( e.getKey() == Ext.EventObject.DELETE ) {
									e.stopEvent();
									inp.clearValue();
								}
							}.createDelegate(this)
						},
						listWidth: 600,
						mode: 'local',
						store: new Ext.data.JsonStore({
							autoLoad: false,
							fields: [
								{ name: 'Evn_id', type: 'int' },
								{ name: 'MedStaffFact_id', type: 'int' },
								{ name: 'LpuSection_id', type: 'int' },
								{ name: 'MedPersonal_id', type: 'int' },
								{ name: 'Evn_Name', type: 'string' },
								{ name: 'Evn_setDate', type: 'date', format: 'd.m.Y' }
							],
							id: 'Evn_id'
						}),
						tabIndex: TABINDEX_EUCPXEF + 1,
						tpl: new Ext.XTemplate(
							'<tpl for="."><div class="x-combo-list-item">',
							'{Evn_Name}&nbsp;',
							'</div></tpl>'
						),
						triggerAction: 'all',
						valueField: 'Evn_id',
						width: 500,
						xtype: 'combo'
					}, {
						border: false,
						layout: 'column',
						items: [{
							border: false,
							layout: 'form',
							items: [{
								allowBlank: false,
								fieldLabel: lang['data_vyipolneniya'],
								format: 'd.m.Y',
								listeners: {
									'change': function(field, newValue, oldValue) {
										if (blockedDateAfterPersonDeath('personpanelid', 'EUCpxEF_PersonInformationFrame', field, newValue, oldValue)) return;
									
										var base_form = this.findById('EvnUslugaComplexEditForm').getForm();

										var lpu_section_id = base_form.findField('LpuSection_uid').getValue();
										var med_staff_fact_id = base_form.findField('MedStaffFact_id').getValue();

										base_form.findField('LpuSection_uid').clearValue();
										base_form.findField('MedStaffFact_id').clearValue();

										if ( !newValue ) {
											setLpuSectionGlobalStoreFilter();
											setMedStaffFactGlobalStoreFilter();
										}
										else {
											setLpuSectionGlobalStoreFilter({
												allowLowLevel: 'yes',
												onDate: Ext.util.Format.date(newValue, 'd.m.Y')
											});

											setMedStaffFactGlobalStoreFilter({
												allowLowLevel: 'yes',
												onDate: Ext.util.Format.date(newValue, 'd.m.Y')
											});
										}

										base_form.findField('LpuSection_uid').getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));
										base_form.findField('MedStaffFact_id').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));

										if ( base_form.findField('LpuSection_uid').getStore().getById(lpu_section_id) ) {
											base_form.findField('LpuSection_uid').setValue(lpu_section_id);
										}

										if ( base_form.findField('MedStaffFact_id').getStore().getById(med_staff_fact_id) ) {
											base_form.findField('MedStaffFact_id').setValue(med_staff_fact_id);
										}
									}.createDelegate(this),
									'keydown': function (inp, e) {
										if ( e.shiftKey == true && e.getKey() == Ext.EventObject.TAB && this.findById('EvnUslugaComplexEditForm').getForm().findField('EvnUslugaComplex_pid').disabled ) {
											e.stopEvent();
											this.buttons[this.buttons.length - 1].focus();
										}
									}.createDelegate(this)
								},
								name: 'EvnUslugaComplex_setDate',
								plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
								tabIndex: TABINDEX_EUCPXEF + 2,
								width: 100,
								xtype: 'swdatefield'
							}]
						}, {
							border: false,
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
								name: 'EvnUslugaComplex_setTime',
								onTriggerClick: function() {
									var base_form = this.findById('EvnUslugaComplexEditForm').getForm();
									var time_field = base_form.findField('EvnUslugaComplex_setTime');

									if ( time_field.disabled ) {
										return false;
									}

									setCurrentDateTime({
										dateField: base_form.findField('EvnUslugaComplex_setDate'),
										loadMask: true,
										setDate: true,
										setDateMaxValue: true,
										setDateMinValue: false,
										setTime: true,
										timeField: time_field,
										windowId: 'EvnUslugaComplexEditWindow'
									});
								}.createDelegate(this),
								plugins: [ new Ext.ux.InputTextMask('99:99', true) ],
								tabIndex: TABINDEX_EUCPXEF + 3,
								validateOnBlur: false,
								width: 60,
								xtype: 'swtimefield'
							}]
						}]
					}, {
						autoHeight: true,
						style: 'padding: 2px 0px 0px 0px;',
						xtype: 'fieldset',
						items: [ new sw.Promed.SwUslugaPlaceCombo({
							allowBlank: false,
							hiddenName: 'UslugaPlace_id',
							lastQuery: '',
							listeners: {
								'change': function(combo, newValue, oldValue) {
									var base_form = this.findById('EvnUslugaComplexEditForm').getForm();
									var record = combo.getStore().getById(newValue);

									var lpu_combo = base_form.findField('Lpu_uid');
									var lpu_section_combo = base_form.findField('LpuSection_uid');
									var med_personal_combo = base_form.findField('MedStaffFact_id');
									var org_combo = base_form.findField('Org_uid');

									lpu_combo.clearValue();
									lpu_section_combo.clearValue();
									med_personal_combo.clearValue();
									org_combo.clearValue();

									if ( !record ) {
										lpu_combo.disable();
										lpu_section_combo.disable();
										med_personal_combo.disable();
										org_combo.disable();
									}
									else if ( record.get('UslugaPlace_Code') == 1 ) {
										lpu_combo.disable();
										lpu_section_combo.enable();
										med_personal_combo.enable();
										org_combo.disable();
									}
									else if ( record.get('UslugaPlace_Code') == 2 ) {
										lpu_combo.enable();
										lpu_section_combo.disable();
										med_personal_combo.disable();
										org_combo.disable();
									}
									else if ( record.get('UslugaPlace_Code') == 3 ) {
										lpu_combo.disable();
										lpu_section_combo.disable();
										med_personal_combo.disable();
										org_combo.enable();
									}
									else {
										lpu_combo.enable();
										lpu_section_combo.disable();
										med_personal_combo.disable();
										org_combo.disable();
									}
								}.createDelegate(this)
							},
							tabIndex: TABINDEX_EUCPXEF + 4,
							width: 500
						}), {
							hiddenName: 'LpuSection_uid',
							id: 'EUCpxEF_LpuSectionCombo',
							lastQuery: '',
							linkedElements: [
								'EUCpxEF_MedPersonalCombo'
							],
							tabIndex: TABINDEX_EUCPXEF + 5,
							width: 500,
							xtype: 'swlpusectionglobalcombo'
						}, {
							displayField: 'Org_Name',
							editable: false,
							enableKeyEvents: true,
							fieldLabel: lang['lpu'],
							hiddenName: 'Lpu_uid',
							listeners: {
								'keydown': function( inp, e ) {
									if ( inp.disabled ) {
										return;
									}

									if ( e.F4 == e.getKey() ) {
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
										inp.onTrigger1Click();
										return false;
									}
								},
								'keyup': function(inp, e) {
									if ( e.F4 == e.getKey() ) {
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
										return false;
									}
								}
							},
							mode: 'local',
							onTrigger1Click: function() {
								var base_form = this.findById('EvnUslugaComplexEditForm').getForm();
								var combo = base_form.findField('Lpu_uid');

								if ( combo.disabled ) {
									return;
								}

								var usluga_place_combo = base_form.findField('UslugaPlace_id');
								var record = usluga_place_combo.getStore().getById(usluga_place_combo.getValue());

								if ( !record ) {
									return false;
								}

								var org_type = 'lpu';

								getWnd('swOrgSearchWindow').show({
									onSelect: function(org_data) {
										if ( org_data.Org_id > 0 ) {
											combo.getStore().loadData([{
												Org_id: org_data.Org_id,
												Org_Name: org_data.Org_Name
											}]);
											combo.setValue(org_data.Org_id);
											getWnd('swOrgSearchWindow').hide();
										}
									},
									onClose: function() {
										combo.focus(true, 200)
									},
									object: org_type
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
							tabIndex: TABINDEX_EUCPXEF + 6,
							tpl: new Ext.XTemplate(
								'<tpl for="."><div class="x-combo-list-item">',
								'{Org_Name}',
								'</div></tpl>'
							),
							trigger1Class: 'x-form-search-trigger',
							triggerAction: 'none',
							valueField: 'Org_id',
							width: 500,
							xtype: 'swbaseremotecombo'
						}, {
							displayField: 'Org_Name',
							editable: false,
							enableKeyEvents: true,
							fieldLabel: lang['drugaya_organizatsiya'],
							hiddenName: 'Org_uid',
							listeners: {
								'keydown': function( inp, e ) {
									if ( inp.disabled ) {
										return;
									}

									if ( e.F4 == e.getKey() ) {
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
										inp.onTrigger1Click();
										return false;
									}
								},
								'keyup': function(inp, e) {
									if ( e.F4 == e.getKey() ) {
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
										return false;
									}
								}
							},
							mode: 'local',
							onTrigger1Click: function() {
								var base_form = this.findById('EvnUslugaComplexEditForm').getForm();
								var combo = base_form.findField('Org_uid');

								if ( combo.disabled ) {
									return;
								}

								var usluga_place_combo = base_form.findField('UslugaPlace_id');
								var usluga_place_id = usluga_place_combo.getValue();
								var record = usluga_place_combo.getStore().getById(usluga_place_id);

								if ( !record ) {
									return false;
								}

								var org_type = 'org';

								getWnd('swOrgSearchWindow').show({
									onSelect: function(org_data) {
										if ( org_data.Org_id > 0 ) {
											combo.getStore().loadData([{
												Org_id: org_data.Org_id,
												Org_Name: org_data.Org_Name
											}]);
											combo.setValue(org_data.Org_id);
											getWnd('swOrgSearchWindow').hide();
										}
									},
									onClose: function() {
										combo.focus(true, 200)
									},
									object: org_type
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
							tabIndex: TABINDEX_EUCPXEF + 7,
							tpl: new Ext.XTemplate(
								'<tpl for="."><div class="x-combo-list-item">',
								'{Org_Name}',
								'</div></tpl>'
							),
							trigger1Class: 'x-form-search-trigger',
							triggerAction: 'none',
							valueField: 'Org_id',
							width: 500,
							xtype: 'swbaseremotecombo'
						}]
					}, {
						autoHeight: true,
						style: 'padding: 2px 0px 0px 0px;',
						title: lang['vrach_vyipolnivshiy_uslugu'],
						xtype: 'fieldset',
						items: [{
							fieldLabel: lang['kod_i_fio_vracha'],
							hiddenName: 'MedStaffFact_id',
							id: 'EUCpxEF_MedPersonalCombo',
							lastQuery: '',
							listWidth: 750,
							parentElementId: 'EUCpxEF_LpuSectionCombo',
							tabIndex: TABINDEX_EUCPXEF + 8,
							width: 500,
							xtype: 'swmedstafffactglobalcombo'
						}]
					},
					new sw.Promed.SwPayTypeCombo({
						allowBlank: false,
						hiddenName: 'PayType_id',
						tabIndex: TABINDEX_EUCPXEF + 9,
						width: 250
					}), {
						allowBlank: false,
						allowNegative: false,
						fieldLabel: lang['kolichestvo'],
						name: 'EvnUslugaComplex_Kolvo',
						tabIndex: TABINDEX_EUCPXEF + 10,
						width: 150,
						xtype: 'numberfield'
					}, {
						allowBlank: false,
						codeField: 'UslugaComplex_Code',
						displayField: 'UslugaComplex_Name',
						enableKeyEvents: true,
						fieldLabel: lang['usluga'],
						forceSelection: true,
						hiddenName: 'UslugaComplex_id',
						listWidth: 600,
						mode: 'local',
						store: new Ext.data.Store({
							autoLoad: false,
							reader: new Ext.data.JsonReader({
								id: 'UslugaComplex_id'
							}, [
								{ name: 'UslugaComplex_Code', mapping: 'UslugaComplex_Code' },
								{ name: 'UslugaComplex_id', mapping: 'UslugaComplex_id' },
								{ name: 'UslugaComplex_Name', mapping: 'UslugaComplex_Name' }
							]),
							url: '/?c=EvnUsluga&m=loadUslugaComplexCombo'
						}),
						tabIndex: TABINDEX_EUCPXEF + 11,
						tpl: new Ext.XTemplate(
							'<tpl for="."><div class="x-combo-list-item">',
							'<table style="border: 0;"><td style="width: 50px"><font color="red">{UslugaComplex_Code}</font></td><td>{UslugaComplex_Name}</td></tr></table>',
							'</div></tpl>'
						),
						valueField: 'UslugaComplex_id',
						width: 500,
						xtype: 'swbaselocalcombo'
					}]
				})]
			})]
		})
		sw.Promed.swEvnUslugaComplexEditWindow.superclass.initComponent.apply(this, arguments);
	},
	keys: [{
		alt: true,
		fn: function(inp, e) {
			var current_window = Ext.getCmp('EvnUslugaComplexEditWindow');

			switch ( e.getKey() ) {
				case Ext.EventObject.C:
					current_window.doSave();
				break;

				case Ext.EventObject.J:
					current_window.onCancelAction();
				break;

				case Ext.EventObject.NUM_ONE:
				case Ext.EventObject.ONE:
					current_window.findById('EUCpxEF_EvnUslugaPanel').toggleCollapse();
				break;
			}
		}.createDelegate(this),
		key: [
			Ext.EventObject.C,
			Ext.EventObject.J,
			Ext.EventObject.NUM_ONE,
			Ext.EventObject.ONE
		],
		stopEvent: true
	}],
	layout: 'border',
	listeners: {
		'hide': function(win) {
			win.onHide();
		},
		'maximize': function(win) {
			win.findById('EUCpxEF_EvnUslugaPanel').doLayout();
		},
		'restore': function(win) {
			win.findById('EUCpxEF_EvnUslugaPanel').doLayout();
		}
	},
	maximizable: true,
	minHeight: 450,
	minWidth: 700,
	modal: true,
	onCancelAction: function() {
		this.hide();
	},
	onHide: Ext.emptyFn,
	parentClass: null,
	plain: true,
	resizable: true,
	show: function() {
		sw.Promed.swEvnUslugaComplexEditWindow.superclass.show.apply(this, arguments);

		this.findById('EUCpxEF_EvnUslugaPanel').expand();

		this.restore();
		this.center();

		var base_form = this.findById('EvnUslugaComplexEditForm').getForm();
		base_form.reset();

		this.callback = Ext.emptyFn;
		this.onHide = Ext.emptyFn;
		this.parentClass = null;

		base_form.findField('EvnUslugaComplex_pid').getStore().removeAll();
		base_form.findField('UslugaComplex_id').getStore().removeAll();

		base_form.findField('Lpu_uid').enable();
		base_form.findField('Org_uid').disable();

		base_form.findField('Lpu_uid').disable();
		base_form.findField('LpuSection_uid').disable();
		base_form.findField('MedStaffFact_id').disable();
		base_form.findField('Org_uid').disable();

		if ( !arguments[0] || !arguments[0].formParams ) {
			sw.swMsg.alert(lang['soobschenie'], lang['nevernyie_parametryi'], function() { this.hide(); }.createDelegate(this) );
			return false;
		}

		if ( arguments[0].callback ) {
			this.callback = arguments[0].callback;
		}

		if ( arguments[0].onHide ) {
			this.onHide = arguments[0].onHide;
		}

		if ( arguments[0].parentClass ) {
			this.parentClass = arguments[0].parentClass;
		}

		if ( arguments[0].parentEvnComboData ) {
			base_form.findField('EvnUslugaComplex_pid').getStore().loadData(arguments[0].parentEvnComboData);
		}

		base_form.setValues(arguments[0].formParams);

		this.findById('EUCpxEF_PersonInformationFrame').load({
			Person_id: (arguments[0].Person_id ? arguments[0].Person_id : ''),
			Person_Birthday: (arguments[0].Person_Birthday ? arguments[0].Person_Birthday : ''),
			Person_Firname: (arguments[0].Person_Firname ? arguments[0].Person_Firname : ''),
			Person_Secname: (arguments[0].Person_Secname ? arguments[0].Person_Secname : ''),
			Person_Surname: (arguments[0].Person_Surname ? arguments[0].Person_Surname : ''),
			callback: function() {
				var field = base_form.findField('EvnUslugaComplex_setDate');
				clearDateAfterPersonDeath('personpanelid', 'EUCpxEF_PersonInformationFrame', field);
			}
		});

		var evn_combo = base_form.findField('EvnUslugaComplex_pid');
		var lpu_combo = base_form.findField('Lpu_uid');
		var lpu_section_combo = base_form.findField('LpuSection_uid');
		var med_personal_combo = base_form.findField('MedStaffFact_id');
		var org_combo = base_form.findField('Org_uid');
		var usluga_complex_combo = base_form.findField('UslugaComplex_id');
		var usluga_place_combo = base_form.findField('UslugaPlace_id');

		var set_date = false;

		evn_combo.enable();
		lpu_combo.disable();
		lpu_section_combo.disable();
		med_personal_combo.disable();
		org_combo.disable();

		if ( this.parentClass == 'EvnPLStom' || this.parentClass == 'EvnVizit' ) {
			evn_combo.disable();
		}

		if ( evn_combo.getStore().getCount() > 0 ) {
			evn_combo.setValue(evn_combo.getStore().getAt(0).get('Evn_id'));
			evn_combo.fireEvent('change', evn_combo, evn_combo.getStore().getAt(0).get('Evn_id'), 0);
		}
		else {
			set_date = true;
		}

		setCurrentDateTime({
			callback: function() {
				if ( set_date ) {
					base_form.findField('EvnUslugaComplex_setDate').fireEvent('change', base_form.findField('EvnUslugaComplex_setDate'), base_form.findField('EvnUslugaComplex_setDate').getValue());
				}
			},
			dateField: base_form.findField('EvnUslugaComplex_setDate'),
			loadMask: false,
			setDate: set_date,
			setDateMaxValue: true,
			setDateMinValue: false,
			setTime: false,
			timeField: base_form.findField('EvnUslugaComplex_setTime'),
			windowId: 'EvnUslugaComplexEditWindow'
		});

		base_form.findField('EvnUslugaComplex_Kolvo').setValue(1);

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: lang['zagruzka_spiska_komleksnyih_uslug'] });
		loadMask.show();

		base_form.findField('UslugaComplex_id').getStore().load({
			callback: function() {
				loadMask.hide();

				if ( evn_combo.disabled ) {
					base_form.findField('EvnUslugaComplex_setDate').focus(true, 250);
				}
				else {
					evn_combo.focus(true, 250);
				}

				base_form.clearInvalid();
			}
		});
	},
	title: WND_POL_EUCPXADD,
	width: 700
});
