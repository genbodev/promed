/**
 * swEvnUslugaCommonEditWindow - окно редактирования/добавления выполнения общей услуги.
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package	  Polka
 * @access	   public
 * @copyright	Copyright (c) 2009 Swan Ltd.
 * @author	   Stas Bykov aka Savage (savage1981@gmail.com)
 * @version	  0.001-28.07.2009
 * @comment	  Префикс для id компонентов EUComEF (EvnUslugaCommonEditForm)
 *
 *
 * @input data: action - действие (add, edit, view)
 *			  parentClass - класс родительского события
 *
 *
 * Использует: окно добавления/редактирования осложнения (swEvnAggEditWindow)
 *			 окно поиска организации (swOrgSearchWindow)
 */

/*NO PARSE JSON*/
sw.Promed.swEvnUslugaCommonEditWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	objectName: 'swEvnUslugaCommonEditWindow',
	objectSrc: '/jscore/Forms/Polka/swEvnUslugaCommonEditWindow.js',

	action: null,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: false,
	closeAction: 'hide',
	collapsible: true,
	deleteEvent: function(event) {
		if ( this.action == 'view' ) {
			return false;
		}

		if ( event != 'EvnAgg' ) {
			return false;
		}

		var error = '';
		var grid = null;
		var question = '';
		var params = new Object();
		var url = '';

		switch ( event ) {
			case 'EvnAgg':
				error = lang['pri_udalenii_oslojneniya_voznikli_oshibki'];
				grid = this.findById('EUComEF_EvnAggGrid');
				question = lang['udalit_oslojnenie'];
				url = '/?c=EvnAgg&m=deleteEvnAgg';
			break;
		}

		if ( !grid || !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get(event + '_id') ) {
			return false;
		}

		var selected_record = grid.getSelectionModel().getSelected();
		params[event + '_id'] = selected_record.get(event + '_id');

		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function(buttonId, text, obj) {
				if ( buttonId == 'yes' ) {
					var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Удаление записи..."});
					loadMask.show();

					Ext.Ajax.request({
						failure: function(response, options) {
							loadMask.hide();
							sw.swMsg.alert(lang['oshibka'], error);
						},
						params: params,
						success: function(response, options) {
							loadMask.hide();

							var response_obj = Ext.util.JSON.decode(response.responseText);

							if ( response_obj.success == false ) {
								sw.swMsg.alert(lang['oshibka'], response_obj.Error_Msg ? response_obj.Error_Msg : error);
							}
							else {
								grid.getStore().remove(selected_record);

								if ( grid.getStore().getCount() == 0 ) {
									grid.getTopToolbar().items.items[1].disable();
									grid.getTopToolbar().items.items[2].disable();
									grid.getTopToolbar().items.items[3].disable();
									LoadEmptyRow(grid);
								}
							}

							grid.getView().focusRow(0);
							grid.getSelectionModel().selectFirstRow();
						},
						url: url
					});
				}
			}.createDelegate(this),
			icon: Ext.MessageBox.QUESTION,
			msg: question,
			title: lang['vopros']
		});
	},
	doSave_default: function(options) {
		// options @Object
		// options.openChildWindow @Function Открыть доченрее окно после сохранения
		if (this.formStatus == 'save' || this.action == 'view') {
			return false;
		}
		this.formStatus = 'save';
		var base_form = this.findById('EvnUslugaCommonEditForm').getForm();
		if (!base_form.isValid()) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					this.formStatus = 'edit';
					base_form.findField('EvnUslugaCommon_setDate').focus(true, 100);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
		var evn_usluga_set_time = base_form.findField('EvnUslugaCommon_setTime').getValue();
		var evn_usluga_common_pid = base_form.findField('EvnUslugaCommon_pid').getValue();
		if ((this.parentClass == 'EvnVizit' || this.parentClass == 'EvnPS') && !evn_usluga_common_pid) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					this.formStatus = 'edit';
					base_form.findField('EvnUslugaCommon_pid').focus(true);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: lang['ne_vyibrano_otdelenie_poseschenie'],
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
		var med_personal_id = null;
		var med_staff_fact_id = base_form.findField('MedStaffFact_id').getValue();
		var params = new Object();
		var record = null;
		var usluga_code = '';
		var usluga_id = 0;
		var usluga_name = '';
		var usluga_place_code = 0;
		record = base_form.findField('MedStaffFact_id').getStore().getById(med_staff_fact_id);
		if (record) {
			base_form.findField('MedPersonal_id').setValue(record.get('MedPersonal_id'));
		}
		record = base_form.findField('UslugaPlace_id').getStore().getById(base_form.findField('UslugaPlace_id').getValue());
		if (record) {
			usluga_place_code = Number(record.get('UslugaPlace_Code'));
			if (getGlobalOptions().region) {
				switch (getGlobalOptions().region.nick) {
					case 'perm': case 'msk':
						switch (usluga_place_code) {
							case 1:
								if (!this.findById('EUComEF_UslugaComplexContainer').hidden) {
									record = base_form.findField('UslugaComplex_id').getStore().getById(base_form.findField('UslugaComplex_id').getValue());
								} else {
									record = base_form.findField('UslugaSpr_id').getStore().getById(base_form.findField('UslugaSpr_id').getValue());
								}
								if (record) {
									usluga_code = record.get('Usluga_Code');
									usluga_id = record.get('Usluga_id');
									usluga_name = record.get('Usluga_Name');
								} else {
									usluga_id = base_form.findField('UslugaSpr_id').getValue();
								}
								break;
							case 2:
							case 3:
								record = base_form.findField('UslugaSpr_id').getStore().getById(base_form.findField('UslugaSpr_id').getValue());
								if (record) {
									usluga_code = record.get('Usluga_Code');
									usluga_id = record.get('Usluga_id');
									usluga_name = record.get('Usluga_Name');
								} else {
									usluga_id = base_form.findField('UslugaSpr_id').getValue();
								}
								break;
						}
						break;
					case 'ufa':
						record = base_form.findField('UslugaSpr_id').getStore().getById(base_form.findField('UslugaSpr_id').getValue());
						if (record) {
							usluga_code = record.get('Usluga_Code');
							usluga_id = record.get('Usluga_id');
							usluga_name = record.get('Usluga_Name');
						}
						break;
				}
			}
		}
		if (!usluga_id) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					this.formStatus = 'edit';
					this.buttons[0].focus();
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: lang['ne_vyibrana_okazyivaemaya_usluga'],
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
		base_form.findField('Usluga_id').setValue(usluga_id);
		var loadMask = new Ext.LoadMask(this.getEl(), {msg: LOAD_WAIT_SAVE});
		loadMask.show();
		// params.MedPersonal_id = med_personal_id;
		params.LpuSection_uid = base_form.findField('LpuSection_uid').getValue();
		if (base_form.findField('EvnUslugaCommon_pid').disabled) {
			params.EvnUslugaCommon_pid = evn_usluga_common_pid;
		}
		base_form.submit({
			failure: function(result_form, action) {
				this.formStatus = 'edit';
				loadMask.hide();
				if (action.result) {
					if (action.result.Alert_Msg) {
						sw.swMsg.show({
							buttons: Ext.Msg.YESNO,
							fn: function(buttonId, text, obj) {
								if ( buttonId == 'yes' ) {
									if (action.result.Error_Code == 102) {
										//
									}

									this.doSave(options);
								}
							}.createDelegate(this),
							icon: Ext.MessageBox.QUESTION,
							msg: action.result.Alert_Msg,
							title: lang['prodoljit_sohranenie']
						});
					} else if (action.result.Error_Msg) {
						sw.swMsg.alert(lang['oshibka'], action.result.Error_Msg);
					} else {
						sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_3]']);
					}
				}
			}.createDelegate(this),
			params: params,
			success: function(result_form, action) {
				this.formStatus = 'edit';
				loadMask.hide();
				if (action.result && action.result.EvnUslugaCommon_id > 0) {
					base_form.findField('EvnUslugaCommon_id').setValue(action.result.EvnUslugaCommon_id);
					if (options && typeof options.openChildWindow == 'function' && this.action == 'add') {
						options.openChildWindow();
					} else {
						var data = new Object();
						var set_time = base_form.findField('EvnUslugaCommon_setTime').getValue();
						if (!set_time || set_time.length == 0) {
							set_time = '00:00';
						}
						data.evnUslugaData = {
							'accessType': 'edit',
							'EvnClass_SysNick': 'EvnUslugaCommon',
							'EvnUsluga_Kolvo': base_form.findField('EvnUslugaCommon_Kolvo').getValue(),
							'EvnUsluga_id': base_form.findField('EvnUslugaCommon_id').getValue(),
							'EvnUsluga_setDate': base_form.findField('EvnUslugaCommon_setDate').getValue(),
							'EvnUsluga_setTime': set_time,
							'Usluga_Code': usluga_code,
							'Usluga_Name': usluga_name
						};
						this.callback(data);
						this.hide();
					}
				} else {
						
					this.callback();
					this.hide();					
				}
			}.createDelegate(this)
		});
	},
	draggable: true,
	enableEdit: function(enable) {
		var base_form = this.findById('EvnUslugaCommonEditForm').getForm();
		var form_fields = new Array('EvnUslugaCommon_Kolvo', 'EvnUslugaCommon_pid', 'EvnUslugaCommon_setDate', 'EvnUslugaCommon_setTime', 'PayType_id', 'UslugaComplex_id', 'UslugaSpr_id', 'UslugaPlace_id');

		var i;
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
	height: 450,
	id: 'EvnUslugaCommonEditWindow',
	initComponent: function() {
		this.EvnXmlPanel = new sw.Promed.EvnXmlPanel({
			autoHeight: true,
			border: true,
			collapsible: true,
			style: "margin-bottom: 0.5em;",
			bodyStyle: 'padding-top: 0.5em;',
			id: 'EUComEF_TemplPanel',
			layout: 'form',
			title: lang['3_spetsifika'],
			ownerWin: this,
			options: {
				XmlType_id: sw.Promed.EvnXml.EVN_USLUGA_PROTOCOL_TYPE_ID, // только протоколы услуг
				EvnClass_id: 22 // документы и шаблоны только категории EvnUslugaCommon
			},
			onAfterLoadData: function(panel){
				var bf = this.findById('EvnUslugaCommonEditForm').getForm();
				//bf.findField('XmlTemplate_id').setValue(panel.getXmlTemplateId());
				panel.expand();
				this.syncSize();
				this.doLayout();
			}.createDelegate(this),
			onAfterClearViewForm: function(panel){
				var bf = this.findById('EvnUslugaCommonEditForm').getForm();
				//bf.findField('XmlTemplate_id').setValue(null);
			}.createDelegate(this),
			// определяем метод, который должен создать посещение перед созданием документа с помощью указанного метода
			onBeforeCreate: function (panel, method, params) {
				if (!panel || !method || typeof panel[method] != 'function') {
					return false;
				}
				var base_form = this.findById('EvnUslugaCommonEditForm').getForm();
				var evn_id_field = base_form.findField('EvnUslugaCommon_id');
				var evn_id = evn_id_field.getValue();
				if (evn_id && evn_id > 0) {
					// услуга была создана ранее
					// все базовые параметры уже должно быть установлены
					panel[method](params);
				} else {
					this.doSave({
						openChildWindow: function() {
							panel.setBaseParams({
								userMedStaffFact: sw.Promed.MedStaffFactByUser.last,
								UslugaComplex_id: base_form.findField('UslugaComplex_id').getValue(),
								Server_id: base_form.findField('Server_id').getValue(),
								Evn_id: evn_id_field.getValue()
							});
							panel[method](params);
						}.createDelegate(this)
					});
				}
				return true;
			}.createDelegate(this)
		});
		
		Ext.apply(this, {
			buttons: [
				{
					handler: function() {
						this.doSave();
					}.createDelegate(this),
					iconCls: 'save16',
					onShiftTabAction: function () {
						if ( !this.findById('EUComEF_EvnAggPanel').collapsed ) {
							this.findById('EUComEF_EvnAggGrid').getView().focusRow(0);
							this.findById('EUComEF_EvnAggGrid').getSelectionModel().selectFirstRow();
						}
						else {
							this.findById('EvnUslugaCommonEditForm').getForm().findField('EvnUslugaCommon_Kolvo').focus(true);
						}
					}.createDelegate(this),
					onTabAction: function () {
						this.buttons[this.buttons.length - 1].focus();
					}.createDelegate(this),
					tabIndex: TABINDEX_EUCOMEF + 25,
					text: BTN_FRMSAVE
				},
				{
					text: '-'
				},
				HelpButton(this, -1),
				{
					handler: function() {
						this.onCancelAction();
					}.createDelegate(this),
					iconCls: 'cancel16',
					onShiftTabAction: function () {
						if (this.action != 'view') {
							this.buttons[0].focus();
						} else if (!this.findById('EUComEF_EvnAggPanel').collapsed) {
							this.findById('EUComEF_EvnAggGrid').getView().focusRow(0);
							this.findById('EUComEF_EvnAggGrid').getSelectionModel().selectFirstRow();
						}
					}.createDelegate(this),
					onTabAction: function () {
						if (!this.findById('EUComEF_EvnUslugaPanel').collapsed && this.action != 'view') {
							if (!this.findById('EvnUslugaCommonEditForm').getForm().findField('EvnUslugaCommon_pid').disabled) {
								this.findById('EvnUslugaCommonEditForm').getForm().findField('EvnUslugaCommon_pid').focus(true, 100);
							} else {
								this.findById('EvnUslugaCommonEditForm').getForm().findField('EvnUslugaCommon_setDate').focus(true, 100);
							}
						} else if (!this.findById('EUComEF_EvnAggPanel').collapsed) {
							this.findById('EUComEF_EvnAggGrid').getView().focusRow(0);
							this.findById('EUComEF_EvnAggGrid').getSelectionModel().selectFirstRow();
						}
					}.createDelegate(this),
					tabIndex: TABINDEX_EUCOMEF + 26,
					text: BTN_FRMCANCEL
				}
			],
			items: [ new sw.Promed.PersonInformationPanelShort({
				id: 'EUComEF_PersonInformationFrame',
				region: 'north'
			}),
				new Ext.form.FormPanel({
					autoScroll: true,
					bodyBorder: false,
					bodyStyle: 'padding: 5px 5px 0',
					border: false,
					frame: false,
					id: 'EvnUslugaCommonEditForm',
					labelAlign: 'right',
					labelWidth: 130,
					layout: 'form',
					reader: new Ext.data.JsonReader({
						success: Ext.emptyFn
					}, [
						{name: 'accessType'},
						{name: 'EvnPrescrProc_id'},
						{name: 'EvnUslugaCommon_id'},
						{name: 'EvnUslugaCommon_Kolvo'},
						{name: 'EvnUslugaCommon_pid'},
						{name: 'EvnUslugaCommon_rid'},
						{name: 'EvnUslugaCommon_setDate'},
						{name: 'EvnUslugaCommon_setTime'},
						{name: 'Lpu_uid'},
						{name: 'LpuSection_uid'},
						{name: 'MedPersonal_id'},
						{name: 'Morbus_id'},
						{name: 'Org_uid'},
						{name: 'PayType_id'},
						{name: 'Person_id'},
						{name: 'PersonEvn_id'},
						{name: 'Server_id'},
						{name: 'Usluga_id'},
						{name: 'UslugaPlace_id'},
						{name: 'EvnUslugaOnkoBeam_disDT'},
						{name: 'OnkoUslugaBeamIrradiationType_id'},
						{name: 'OnkoUslugaBeamKindType_id'},
						{name: 'OnkoUslugaBeamMethodType_id'},
						{name: 'OnkoUslugaBeamRadioModifType_id'},
						{name: 'OnkoUslugaBeamFocusType_id'},
						{name: 'EvnUslugaOnkoBeam_TotalDoseTumor'},
						{name: 'EvnUslugaOnkoBeam_TotalDoseRegZone'},
						{name: 'OnkoUslugaBeamUnitType_id'},
						{name: 'OnkoUslugaBeamUnitType_did'},
						{name: 'EvnUslugaOnkoChem_disDT'},
						{name: 'OnkoUslugaChemKindType_id'},
						{name: 'OnkoUslugaChemFocusType_id'},
						{name: 'EvnUslugaOnkoChem_Dose'},
						{name: 'EvnUslugaOnkoGormun_setDT'},
						{name: 'EvnUslugaOnkoGormun_disDT'},
						{name: 'EvnUslugaOnkoGormun_IsDrug'},
						{name: 'EvnUslugaOnkoGormun_IsSurgical'},
						{name: 'EvnUslugaOnkoGormun_IsBeam'},
						{name: 'OnkoUslugaGormunFocusType_id'},
						{name: 'OnkoDrug_id'},
						{name: 'EvnUslugaOnkoGormun_Dose'}
					]),
					region: 'center',
					default_url: '/?c=EvnUsluga&m=saveEvnUslugaCommon',
					default_formLoadUrl: '/?c=EvnUsluga&m=loadEvnUslugaEditForm',
					items: [
						{
							name: 'accessType',
							value: '',
							xtype: 'hidden'
						},
						{
							name: 'EvnPrescrProc_id',
							value: 0,
							xtype: 'hidden'
						},
						{
							name: 'EvnClass_SysNick',
							value: 0,
							xtype: 'hidden'
						},
						{
							name: 'EvnUslugaCommon_id',
							value: 0,
							xtype: 'hidden'
						},
						{
							name: 'EvnUslugaCommon_rid',
							value: 0,
							xtype: 'hidden'
						},
						{
							name: 'MedPersonal_id',
							value: 0,
							xtype: 'hidden'
						},
						{
							name: 'Morbus_id',
							value: -1,
							xtype: 'hidden'
						},
						{
							name: 'Person_id',
							value: 0,
							xtype: 'hidden'
						},
						{
							name: 'PersonEvn_id',
							value: 0,
							xtype: 'hidden'
						},
						{
							name: 'Server_id',
							value: -1,
							xtype: 'hidden'
						},
						{
							id: 'EUComEF_Usluga_id',
							name: 'Usluga_id',
							value: 0,
							xtype: 'hidden'
						},
						new sw.Promed.Panel({
							autoHeight: true,
							// bodyStyle: 'padding: 0.5em;',
							border: true,
							collapsible: true,
							id: 'EUComEF_EvnUslugaPanel',
							layout: 'form',
							style: 'margin-bottom: 0.5em;',
							title: lang['1_usluga'],
							items: [
								{
									// allowBlank: false,
									displayField: 'Evn_Name',
									editable: false,
									enableKeyEvents: true,
									fieldLabel: lang['otdelenie_poseschenie'],
									hiddenName: 'EvnUslugaCommon_pid',
									listeners: {
										'change': function(combo, newValue, oldValue) {
											var base_form = this.findById('EvnUslugaCommonEditForm').getForm();
											var record = combo.getStore().getById(newValue);
											if (record) {
												var MedStaffFact_id = record.get('MedStaffFact_id');
												var lpu_section_id = record.get('LpuSection_id');
												var lpu_section_pid;
												base_form.findField('EvnUslugaCommon_setDate').setValue(record.get('Evn_setDate'));
												base_form.findField('EvnUslugaCommon_setDate').fireEvent('change', base_form.findField('EvnUslugaCommon_setDate'), record.get('Evn_setDate'), 0);
												base_form.findField('UslugaPlace_id').setValue(1);
												base_form.findField('UslugaPlace_id').fireEvent('change', base_form.findField('UslugaPlace_id'), 1, 0);
												if (base_form.findField('LpuSection_uid').getStore().getById(lpu_section_id)) {
													base_form.findField('LpuSection_uid').setValue(lpu_section_id);
													base_form.findField('LpuSection_uid').fireEvent('change', base_form.findField('LpuSection_uid'), lpu_section_id);
													lpu_section_pid = base_form.findField('LpuSection_uid').getStore().getById(lpu_section_id).get('LpuSection_pid');
												}
												var index = base_form.findField('MedStaffFact_id').getStore().findBy(function(rec) {
													return (rec.get('MedStaffFact_id') == MedStaffFact_id);
												});
												if (index >= 0) {
													base_form.findField('MedStaffFact_id').setValue(MedStaffFact_id);
												}
											}
										}.createDelegate(this),
										'keydown': function (inp, e) {
											if (e.shiftKey == true && e.getKey() == Ext.EventObject.TAB) {
												e.stopEvent();
												this.buttons[this.buttons.length - 1].focus();
											} else if (e.getKey() == Ext.EventObject.DELETE) {
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
											{name: 'Evn_id', type: 'int'},
											{name: 'MedStaffFact_id', type: 'int'},
											{name: 'LpuSection_id', type: 'int'},
											{name: 'MedPersonal_id', type: 'int'},
											{name: 'Evn_Name', type: 'string'},
											{name: 'Evn_setDate', type: 'date', format: 'd.m.Y'}
										],
										id: 'Evn_id'
									}),
									tabIndex: TABINDEX_EUCOMEF + 1,
									tpl: new Ext.XTemplate('<tpl for="."><div class="x-combo-list-item">', '{Evn_Name}&nbsp;', '</div></tpl>'),
									triggerAction: 'all',
									valueField: 'Evn_id',
									width: 500,
									xtype: 'combo'
								},
								{
									border: false,
									layout: 'column',
									items: [
										{
											border: false,
											layout: 'form',
											items: [
												{
													allowBlank: false,
													fieldLabel: lang['data_vyipolneniya'],
													format: 'd.m.Y',
													listeners: {
														'change': function(field, newValue, oldValue) {
															if (blockedDateAfterPersonDeath('personpanelid', 'EUComEF_PersonInformationFrame', field, newValue, oldValue)) return;
															var base_form = this.findById('EvnUslugaCommonEditForm').getForm();
															var lpu_section_id = base_form.findField('LpuSection_uid').getValue();
															var med_staff_fact_id = base_form.findField('MedStaffFact_id').getValue();
															base_form.findField('LpuSection_uid').clearValue();
															base_form.findField('MedStaffFact_id').clearValue();
															base_form.findField('UslugaSpr_id').disable();
															var section_filter_params = {};
															var medstafffact_filter_params = {};
															var user_med_staff_fact_id = this.UserMedStaffFact_id;
															var user_lpu_section_id = this.UserLpuSection_id;
															var user_med_staff_facts = this.UserMedStaffFacts;
															var user_lpu_sections = this.UserLpuSections;
															medstafffact_filter_params.allowLowLevel = 'yes';
															section_filter_params.allowLowLevel = 'yes';
															var OnkoUslugaList = '1012820, 1012821, 1012822';
															if (newValue) {
																if (OnkoUslugaList.indexOf(base_form.findField('UslugaSpr_id').getValue()) == -1 || base_form.findField('UslugaSpr_id').getValue() == 0 ) {
																	base_form.findField('UslugaSpr_id').enable();
																}
																section_filter_params.onDate = Ext.util.Format.date(newValue, 'd.m.Y');
																medstafffact_filter_params.onDate = Ext.util.Format.date(newValue, 'd.m.Y');
																if (this.action == 'add')
																	base_form.findField('UslugaSpr_id').setFilterActualUsluga(newValue, null);
		
																if (this.findById('EUComEF_EvnUslugaOnkoBeam_setDT')) {
																	this.findById('EUComEF_EvnUslugaOnkoBeam_setDT').setValue(newValue);
																} 
																else if (this.findById('EUComEF_EvnUslugaOnkoChem_setDT')) {
																	this.findById('EUComEF_EvnUslugaOnkoChem_setDT').setValue(newValue);
																} 
																else if (this.findById('EUComEF_EvnUslugaOnkoGormun_setDT')) {
																	this.findById('EUComEF_EvnUslugaOnkoGormun_setDT').setValue(newValue);
																}
															}
															// фильтр или на конкретное место работы или на список мест работы
															if (user_med_staff_fact_id && user_lpu_section_id && this.action == 'add') {
																section_filter_params.id = user_lpu_section_id;
																medstafffact_filter_params.id = user_med_staff_fact_id;
															} else if (user_med_staff_facts && user_lpu_sections && this.action == 'add') {
																section_filter_params.ids = user_lpu_sections;
																medstafffact_filter_params.ids = user_med_staff_facts;
															}
															setLpuSectionGlobalStoreFilter(section_filter_params);
															setMedStaffFactGlobalStoreFilter(medstafffact_filter_params);
															base_form.findField('LpuSection_uid').getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));
															base_form.findField('MedStaffFact_id').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));
															if (base_form.findField('LpuSection_uid').getStore().getById(lpu_section_id)) {
																base_form.findField('LpuSection_uid').setValue(lpu_section_id);
																base_form.findField('LpuSection_uid').fireEvent('change', base_form.findField('LpuSection_uid'), lpu_section_id);
															}
															if (base_form.findField('MedStaffFact_id').getStore().getById(med_staff_fact_id)) {
																base_form.findField('MedStaffFact_id').setValue(med_staff_fact_id);
															}
															/*
															 если форма отурыта на редактирование и задано отделение и
															 место работы или задан список мест работы, то не даем редактировать вообще
															 */
															if (this.action == 'edit' && (( user_med_staff_fact_id && user_lpu_section_id ) || ( this.UserMedStaffFacts && this.UserMedStaffFacts.length > 0 ))) {
																base_form.findField('LpuSection_uid').disable();
																base_form.findField('MedStaffFact_id').disable();
															}
															/*
															 если форма отурыта на добавление и задано отделение и
															 место работы, то устанавливаем их не даем редактировать вообще
															 */
															if (this.action == 'add' && user_med_staff_fact_id && user_lpu_section_id) {
																base_form.findField('LpuSection_uid').setValue(user_lpu_section_id);
																base_form.findField('LpuSection_uid').disable();
																base_form.findField('MedStaffFact_id').setValue(user_med_staff_fact_id);
																base_form.findField('MedStaffFact_id').disable();
															} else
															/*
															 если форма отурыта на добавление и задан список отделений и
															 мест работы, но он состоит из одного элемета,
															 то устанавливаем значение и не даем редактировать
															 */
															if (this.action == 'add' && this.UserMedStaffFacts && this.UserMedStaffFacts.length == 1) {
																// список состоит из одного элемента (устанавливаем значение и не даем редактировать)
																base_form.findField('LpuSection_uid').setValue(this.UserLpuSections[0]);
																base_form.findField('LpuSection_uid').disable();
																base_form.findField('MedStaffFact_id').setValue(this.UserMedStaffFacts[0]);
																base_form.findField('MedStaffFact_id').disable();
															}
														}.createDelegate(this),
														'keydown': function (inp, e) {
															if (e.shiftKey == true && e.getKey() == Ext.EventObject.TAB && this.findById('EvnUslugaCommonEditForm').getForm().findField('EvnUslugaCommon_pid').disabled) {
																e.stopEvent();
																this.buttons[this.buttons.length - 1].focus();
															}
														}.createDelegate(this)
													},
													name: 'EvnUslugaCommon_setDate',
													plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
													tabIndex: TABINDEX_EUCOMEF + 2,
													width: 100,
													xtype: 'swdatefield'
												}
											]
										},
										{
											border: false,
											layout: 'form',
											items: [
												{
													fieldLabel: lang['vremya'],
													listeners: {
														'keydown': function (inp, e) {
															if (e.getKey() == Ext.EventObject.F4) {
																e.stopEvent();
																inp.onTriggerClick();
															}
														}
													},
													name: 'EvnUslugaCommon_setTime',
													onTriggerClick: function() {
														var base_form = this.findById('EvnUslugaCommonEditForm').getForm();
														var time_field = base_form.findField('EvnUslugaCommon_setTime');
														if (time_field.disabled) {
															return false;
														}
														setCurrentDateTime({
															dateField: base_form.findField('EvnUslugaCommon_setDate'),
															loadMask: true,
															setDate: true,
															setDateMaxValue: true,
															setDateMinValue: false,
															setTime: true,
															timeField: time_field,
															windowId: 'EvnUslugaCommonEditWindow'
														});
													}.createDelegate(this),
													plugins: [ new Ext.ux.InputTextMask('99:99', true) ],
													tabIndex: TABINDEX_EUCOMEF + 3,
													validateOnBlur: false,
													width: 60,
													xtype: 'swtimefield'
												}
											]
										}
									]
								},
								{
									autoHeight: true,
									style: 'padding: 2px 0px 0px 0px;',
									xtype: 'fieldset',
									items: [
										new sw.Promed.SwUslugaPlaceCombo({
											allowBlank: false,
											hiddenName: 'UslugaPlace_id',
											lastQuery: '',
											listeners: {
												'change': function(combo, newValue, oldValue) {
													var base_form = this.findById('EvnUslugaCommonEditForm').getForm();
													var record = combo.getStore().getById(newValue);
													var lpu_combo = base_form.findField('Lpu_uid');
													var lpu_section_combo = base_form.findField('LpuSection_uid');
													var med_personal_combo = base_form.findField('MedStaffFact_id');
													var org_combo = base_form.findField('Org_uid');
													lpu_combo.clearValue();
													lpu_section_combo.clearValue();
													med_personal_combo.clearValue();
													org_combo.clearValue();
													lpu_combo.setAllowBlank(true);
													lpu_section_combo.setAllowBlank(true);
													med_personal_combo.setAllowBlank(true);
													org_combo.setAllowBlank(true);
													// Вызываем событие change для списка отделений, чтобы сбросить фильтр услуг по коду профиля отлделения (Уфа)
													// и очистить справочник услуг, указанных для отделения в структуре (Пермь)
													lpu_section_combo.fireEvent('change', lpu_section_combo, null);
													if (!record) {
														lpu_combo.disable();
														lpu_section_combo.disable();
														med_personal_combo.disable();
														org_combo.disable();
													} else {
														switch (parseInt(record.get('UslugaPlace_Code'))) {
															case 1:
																lpu_combo.disable();
																lpu_section_combo.enable();
																med_personal_combo.enable();
																org_combo.disable();
																lpu_section_combo.setAllowBlank(false);
																med_personal_combo.setAllowBlank(false);
																break;
															case 2:
																lpu_combo.enable();
																lpu_section_combo.disable();
																med_personal_combo.disable();
																org_combo.disable();
																lpu_combo.setAllowBlank(false);
																break;
															case 3:
																lpu_combo.disable();
																lpu_section_combo.disable();
																med_personal_combo.disable();
																org_combo.enable();
																org_combo.setAllowBlank(false);
																break;
														}
													}
												}.createDelegate(this)
											},
											tabIndex: TABINDEX_EUCOMEF + 4,
											width: 500
										}), {
											hiddenName: 'LpuSection_uid',
											id: 'EUComEF_LpuSectionCombo',
											lastQuery: '',
											linkedElements: [
												'EUComEF_MedPersonalCombo'
											],
											tabIndex: TABINDEX_EUCOMEF + 5,
											width: 500,
											xtype: 'swlpusectionglobalcombo'
										}, {
											displayField: 'Org_Name',
											editable: false,
											enableKeyEvents: true,
											fieldLabel: lang['lpu'],
											hiddenName: 'Lpu_uid',
											listeners: {
												'keydown': function(inp, e) {
													if (inp.disabled) {
														return;
													}
													if (e.F4 == e.getKey()) {
														if (e.browserEvent.stopPropagation) {
															e.browserEvent.stopPropagation();
														} else {
															e.browserEvent.cancelBubble = true;
														}
														if (e.browserEvent.preventDefault) {
															e.browserEvent.preventDefault();
														} else {
															e.browserEvent.returnValue = false;
														}
														e.returnValue = false;
														if (Ext.isIE) {
															e.browserEvent.keyCode = 0;
															e.browserEvent.which = 0;
														}
														inp.onTrigger1Click();
														return false;
													}
												},
												'keyup': function(inp, e) {
													if (e.F4 == e.getKey()) {
														if (e.browserEvent.stopPropagation) {
															e.browserEvent.stopPropagation();
														} else {
															e.browserEvent.cancelBubble = true;
														}
														if (e.browserEvent.preventDefault) {
															e.browserEvent.preventDefault();
														} else {
															e.browserEvent.returnValue = false;
														}
														e.returnValue = false;
														if (Ext.isIE) {
															e.browserEvent.keyCode = 0;
															e.browserEvent.which = 0;
														}
														return false;
													}
												}
											},
											mode: 'local',
											onTrigger1Click: function() {
												var base_form = this.findById('EvnUslugaCommonEditForm').getForm();
												var combo = base_form.findField('Lpu_uid');
												if (combo.disabled) {
													return;
												}
												var usluga_place_combo = base_form.findField('UslugaPlace_id');
												var record = usluga_place_combo.getStore().getById(usluga_place_combo.getValue());
												if (!record) {
													return false;
												}
												var org_type = 'lpu';
												getWnd('swOrgSearchWindow').show({
													onSelect: function(org_data) {
														if (org_data.Org_id > 0) {
															combo.getStore().loadData([
																{
																	Org_id: org_data.Org_id,
																	Org_Name: org_data.Org_Name
																}
															]);
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
													{name: 'Org_id', type: 'int'},
													{name: 'Org_Name', type: 'string'}
												],
												key: 'Org_id',
												sortInfo: {
													field: 'Org_Name'
												},
												url: C_ORG_LIST
											}),
											tabIndex: TABINDEX_EUCOMEF + 6,
											tpl: new Ext.XTemplate('<tpl for="."><div class="x-combo-list-item">', '{Org_Name}', '</div></tpl>'),
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
												'keydown': function(inp, e) {
													if (inp.disabled) {
														return;
													}
													if (e.F4 == e.getKey()) {
														if (e.browserEvent.stopPropagation) {
															e.browserEvent.stopPropagation();
														} else {
															e.browserEvent.cancelBubble = true;
														}
														if (e.browserEvent.preventDefault) {
															e.browserEvent.preventDefault();
														} else {
															e.browserEvent.returnValue = false;
														}
														e.returnValue = false;
														if (Ext.isIE) {
															e.browserEvent.keyCode = 0;
															e.browserEvent.which = 0;
														}
														inp.onTrigger1Click();
														return false;
													}
												},
												'keyup': function(inp, e) {
													if (e.F4 == e.getKey()) {
														if (e.browserEvent.stopPropagation) {
															e.browserEvent.stopPropagation();
														} else {
															e.browserEvent.cancelBubble = true;
														}
														if (e.browserEvent.preventDefault) {
															e.browserEvent.preventDefault();
														} else {
															e.browserEvent.returnValue = false;
														}
														e.returnValue = false;
														if (Ext.isIE) {
															e.browserEvent.keyCode = 0;
															e.browserEvent.which = 0;
														}
														return false;
													}
												}
											},
											mode: 'local',
											onTrigger1Click: function() {
												var base_form = this.findById('EvnUslugaCommonEditForm').getForm();
												var combo = base_form.findField('Org_uid');
												if (combo.disabled) {
													return;
												}
												var usluga_place_combo = base_form.findField('UslugaPlace_id');
												var usluga_place_id = usluga_place_combo.getValue();
												var record = usluga_place_combo.getStore().getById(usluga_place_id);
												if (!record) {
													return false;
												}
												var org_type = 'org';
												getWnd('swOrgSearchWindow').show({
													onSelect: function(org_data) {
														if (org_data.Org_id > 0) {
															combo.getStore().loadData([
																{
																	Org_id: org_data.Org_id,
																	Org_Name: org_data.Org_Name
																}
															]);
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
													{name: 'Org_id', type: 'int'},
													{name: 'Org_Name', type: 'string'}
												],
												key: 'Org_id',
												sortInfo: {
													field: 'Org_Name'
												},
												url: C_ORG_LIST
											}),
											tabIndex: TABINDEX_EUCOMEF + 7,
											tpl: new Ext.XTemplate('<tpl for="."><div class="x-combo-list-item">', '{Org_Name}', '</div></tpl>'),
											trigger1Class: 'x-form-search-trigger',
											triggerAction: 'none',
											valueField: 'Org_id',
											width: 500,
											xtype: 'swbaseremotecombo'
										}]
								},
								{
									autoHeight: true,
									style: 'padding: 2px 0px 0px 0px;',
									title: lang['vrach_vyipolnivshiy_uslugu'],
									xtype: 'fieldset',
									items: [
										{
											fieldLabel: lang['kod_i_fio_vracha'],
											hiddenName: 'MedStaffFact_id',
											id: 'EUComEF_MedPersonalCombo',
											lastQuery: '',
											listWidth: 750,
											parentElementId: 'EUComEF_LpuSectionCombo',
											tabIndex: TABINDEX_EUCOMEF + 8,
											width: 500,
											xtype: 'swmedstafffactglobalcombo'
										}
									]
								},
								{
									border: false,
									id: 'EUComEF_UslugaSprContainer',
									layout: 'form',
									items: [
										{
											allowedCatCode: (getGlobalOptions().region && getGlobalOptions().region.nick == 'ufa' ? 1 : null),
											fieldLabel: lang['usluga'],
											hiddenName: 'UslugaSpr_id',
											listWidth: 600,
											tabIndex: TABINDEX_EUCOMEF + 9,
											width: 500,
											listeners: {
												'beforequery': function(event) {
													var usluga_date_field = this.findById('EvnUslugaCommonEditForm').getForm().findField('EvnUslugaCommon_setDate');
													var usluga_date = usluga_date_field.getValue();
													if (!usluga_date) {
														sw.swMsg.alert(lang['oshibka'], lang['vyi_ne_ukazali_datu_vyipolneniya_uslugi'], function() {
															usluga_date_field.focus();
														});
														return false;
													}
													if (event.combo.Usluga_date != Ext.util.Format.date(usluga_date, 'd.m.Y'))
														event.combo.setFilterActualUsluga(usluga_date, null);
												}.createDelegate(this)
											},
											xtype: 'swuslugacombo'
										}
									]
								},
								{
									border: false,
									id: 'EUComEF_UslugaComplexContainer',
									layout: 'form',
									items: [
										{
											fieldLabel: lang['usluga'],
											hiddenName: 'UslugaComplex_id',
											id: 'EUStomEF_UslugaComplexCombo',
											listeners: {
												'blur': function(combo) {
													var combo_raw_value = combo.getRawValue();
													var combo_value = combo.getValue();
													var record = combo.getStore().getById(combo_value);
													if (!record || (record.get('Usluga_Code') + ". " + record.get('Usluga_Name') != combo_raw_value)) {
														combo.clearValue();
													}
												},
												'change': function(combo, newValue, oldValue) {
													var base_form = this.findById('EvnUslugaCommonEditForm').getForm();

													var kolvo = base_form.findField('EvnUslugaCommon_Kolvo').getValue();
													var record = combo.getStore().getById(newValue);

													if ( record ) {
														if ( !kolvo ) {
															kolvo = 1;
														}

														base_form.findField('Usluga_id').setValue(record.get('Usluga_id'));
														base_form.findField('EvnUslugaCommon_Kolvo').setValue(kolvo);
													}
													else {
														base_form.findField('Usluga_id').setValue(0);
													}
												}.createDelegate(this),
												'select': function(combo, record, index) {
													if (record.get(combo.valueField)) {
														combo.setRawValue(record.get('Usluga_Code') + ". " + record.get('Usluga_Name'));
													}
												}.createDelegate(this)
											},
											listWidth: 600,
											tabIndex: TABINDEX_EUCOMEF + 10,
											width: 500,
											xtype: 'swuslugacomplexcombo'
										}
									]
								}, {
									allowBlank: false,
									tabIndex: TABINDEX_EUCOMEF + 11,
									typeCode: 'int',
									useCommonFilter: true,
									width: 250,
									xtype: 'swpaytypecombo'
								}, {
									allowBlank: false,
									allowNegative: false,
									enableKeyEvents: true,
									fieldLabel: lang['kolichestvo'],
									listeners: {
										'keydown': function (inp, e) {
											if (e.shiftKey == false && e.getKey() == Ext.EventObject.TAB && !this.findById('EUComEF_EvnAggPanel').collapsed) {
												e.stopEvent();
												this.findById('EUComEF_EvnAggGrid').getView().focusRow(0);
												this.findById('EUComEF_EvnAggGrid').getSelectionModel().selectFirstRow();
											}
										}.createDelegate(this)
									},
									name: 'EvnUslugaCommon_Kolvo',
									tabIndex: TABINDEX_EUCOMEF + 13,
									width: 150,
									xtype: 'numberfield'
								}
							]
						}),
						new sw.Promed.Panel({
							border: true,
							collapsible: true,
							height: 200,
							id: 'EUComEF_EvnAggPanel',
							isLoaded: false,
							layout: 'border',
							listeners: {
								'expand': function(panel) {
									if (panel.isLoaded === false) {
										panel.isLoaded = true;
										panel.findById('EUComEF_EvnAggGrid').getStore().load({
											params: {
												EvnAgg_pid: this.findById('EvnUslugaCommonEditForm').getForm().findField('EvnUslugaCommon_id').getValue()
											}
										});
									}
									panel.doLayout();
								}.createDelegate(this)
							},
							// style: 'margin-bottom: 0.5em;',
							title: lang['2_oslojneniya'],
							items: [ new Ext.grid.GridPanel({
								autoExpandColumn: 'autoexpand',
								autoExpandMin: 100,
								border: false,
								columns: [
									{
										dataIndex: 'AggType_Name',
										header: lang['vid_oslojneniya'],
										hidden: false,
										id: 'autoexpand',
										sortable: true
									},
									{
										dataIndex: 'AggWhen_Name',
										header: lang['kontekst_oslojneniya'],
										hidden: false,
										resizable: false,
										sortable: true,
										width: 200
									},
									{
										dataIndex: 'EvnAgg_setDate',
										header: lang['data_oslojneniya'],
										hidden: false,
										renderer: Ext.util.Format.dateRenderer('d.m.Y'),
										resizable: false,
										sortable: true,
										width: 130
									}
								],
								frame: false,
								id: 'EUComEF_EvnAggGrid',
								keys: [
									{
										key: [
											Ext.EventObject.DELETE,
											Ext.EventObject.END,
											Ext.EventObject.ENTER,
											Ext.EventObject.F3,
											Ext.EventObject.F4,
											Ext.EventObject.HOME,
											Ext.EventObject.INSERT,
											Ext.EventObject.PAGE_DOWN,
											Ext.EventObject.PAGE_UP,
											Ext.EventObject.TAB
										],
										fn: function(inp, e) {
											e.stopEvent();
											if (e.browserEvent.stopPropagation)
												e.browserEvent.stopPropagation(); else
												e.browserEvent.cancelBubble = true;
											if (e.browserEvent.preventDefault)
												e.browserEvent.preventDefault(); else
												e.browserEvent.returnValue = false;
											e.returnValue = false;
											if (Ext.isIE) {
												e.browserEvent.keyCode = 0;
												e.browserEvent.which = 0;
											}
											var grid = this.findById('EUComEF_EvnAggGrid');
											switch (e.getKey()) {
												case Ext.EventObject.DELETE:
													this.deleteEvent('EvnAgg');
													break;
												case Ext.EventObject.END:
													GridEnd(grid);
													break;
												case Ext.EventObject.ENTER:
												case Ext.EventObject.F3:
												case Ext.EventObject.F4:
												case Ext.EventObject.INSERT:
													if (!grid.getSelectionModel().getSelected()) {
														return false;
													}
													var action = 'add';
													if (e.getKey() == Ext.EventObject.F3) {
														action = 'view';
													} else if (e.getKey() == Ext.EventObject.F4 || e.getKey() == Ext.EventObject.ENTER) {
														action = 'edit';
													}
													this.openEvnAggEditWindow(action);
													break;
												case Ext.EventObject.HOME:
													GridHome(grid);
													break;
												case Ext.EventObject.PAGE_DOWN:
													GridPageDown(grid);
													break;
												case Ext.EventObject.PAGE_UP:
													GridPageUp(grid);
													break;
												case Ext.EventObject.TAB:
													var base_form = this.findById('EvnUslugaCommonEditForm').getForm();
													grid.getSelectionModel().clearSelections();
													grid.getSelectionModel().fireEvent('rowselect', grid.getSelectionModel());
													if (e.shiftKey == false) {
														if (this.action == 'view') {
															this.buttons[this.buttons.length - 1].focus();
														} else {
															this.buttons[0].focus();
														}
													} else {
														if (!this.findById('EUComEF_EvnUslugaPanel').collapsed && this.action != 'view') {
															base_form.findField('EvnUslugaCommon_Kolvo').focus(true);
														} else {
															this.buttons[this.buttons.length - 1].focus();
														}
													}
													break;
											}
										},
										scope: this,
										stopEvent: true
									}
								],
								listeners: {
									'rowdblclick': function(grid, number, obj) {
										this.openEvnAggEditWindow('edit');
									}.createDelegate(this)
								},
								loadMask: true,
								region: 'center',
								sm: new Ext.grid.RowSelectionModel({
									listeners: {
										'rowselect': function(sm, rowIndex, record) {
											var access_type = 'view';
											var id = null;
											var selected_record = sm.getSelected();
											var toolbar = this.findById('EUComEF_EvnAggGrid').getTopToolbar();
											if (selected_record) {
												access_type = selected_record.get('accessType');
												id = selected_record.get('EvnAgg_id');
											}
											toolbar.items.items[1].disable();
											toolbar.items.items[3].disable();
											if (id) {
												toolbar.items.items[2].enable();
												if (this.action != 'view' && access_type == 'edit') {
													toolbar.items.items[1].enable();
													toolbar.items.items[3].enable();
												}
											} else {
												toolbar.items.items[2].disable();
											}
										}.createDelegate(this)
									}
								}),
								stripeRows: true,
								store: new Ext.data.Store({
									autoLoad: false,
									listeners: {
										'load': function(store, records, index) {
											if (store.getCount() == 0) {
												LoadEmptyRow(this.findById('EUComEF_EvnAggGrid'));
											}
											// this.findById('EUComEF_EvnAggGrid').getView().focusRow(0);
											// this.findById('EUComEF_EvnAggGrid').getSelectionModel().selectFirstRow();
										}.createDelegate(this)
									},
									reader: new Ext.data.JsonReader({
										id: 'EvnAgg_id'
									}, [
										{
											mapping: 'accessType',
											name: 'accessType',
											type: 'string'
										},
										{
											mapping: 'EvnAgg_id',
											name: 'EvnAgg_id',
											type: 'int'
										},
										{
											mapping: 'EvnAgg_pid',
											name: 'EvnAgg_pid',
											type: 'int'
										},
										{
											mapping: 'Person_id',
											name: 'Person_id',
											type: 'int'
										},
										{
											mapping: 'PersonEvn_id',
											name: 'PersonEvn_id',
											type: 'int'
										},
										{
											mapping: 'Server_id',
											name: 'Server_id',
											type: 'int'
										},
										{
											mapping: 'AggType_id',
											name: 'AggType_id',
											type: 'int'
										},
										{
											mapping: 'AggWhen_id',
											name: 'AggWhen_id',
											type: 'int'
										},
										{
											mapping: 'AggType_Name',
											name: 'AggType_Name',
											type: 'string'
										},
										{
											mapping: 'AggWhen_Name',
											name: 'AggWhen_Name',
											type: 'string'
										},
										{
											dateFormat: 'd.m.Y',
											mapping: 'EvnAgg_setDate',
											name: 'EvnAgg_setDate',
											type: 'date'
										},
										{
											mapping: 'EvnAgg_setTime',
											name: 'EvnAgg_setTime',
											type: 'string'
										}
									]),
									url: '/?c=EvnAgg&m=loadEvnAggGrid'
								}),
								tbar: new sw.Promed.Toolbar({
									buttons: [
										{
											handler: function() {
												this.openEvnAggEditWindow('add');
											}.createDelegate(this),
											iconCls: 'add16',
											text: BTN_GRIDADD
										},
										{
											handler: function() {
												this.openEvnAggEditWindow('edit');
											}.createDelegate(this),
											iconCls: 'edit16',
											text: BTN_GRIDEDIT
										},
										{
											handler: function() {
												this.openEvnAggEditWindow('view');
											}.createDelegate(this),
											iconCls: 'view16',
											text: BTN_GRIDVIEW
										},
										{
											handler: function() {
												this.deleteEvent('EvnAgg');
											}.createDelegate(this),
											iconCls: 'delete16',
											text: BTN_GRIDDEL
										}
									]
								})
							})]
						}),
						{
							id: 'EUComEF_EvnUslugaOnkoPanel',
							layout: 'form',
							border: false,		
							bodyStyle: 'padding-top: 0.5em;'
						},
						this.EvnXmlPanel
					]
				})
			]
		});

		sw.Promed.swEvnUslugaCommonEditWindow.superclass.initComponent.apply(this, arguments);
		
		this.findById('EUComEF_LpuSectionCombo').addListener('change', function(combo, newValue, oldValue) {
			var enable_usluga_section_load = getUslugaOptions().enable_usluga_section_load;
			if (getGlobalOptions().region) {
				var base_form = this.findById('EvnUslugaCommonEditForm').getForm();
				switch (getGlobalOptions().region.nick) {
					case 'perm':
						if (newValue && enable_usluga_section_load && this.parentClass != 'EvnPrescr' && base_form.findField('EvnClass_SysNick').getValue() == 'EvnUslugaCommon' ) {
							var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Загрузка списка услуг отделения..."});
							loadMask.show();
							base_form.findField('UslugaComplex_id').getStore().load({
								callback: function() {
									loadMask.hide();
									if (base_form.findField('UslugaComplex_id').getStore().getCount() > 0) {
										this.findById('EUComEF_UslugaComplexContainer').show();
										this.findById('EUComEF_UslugaSprContainer').hide();
									} else {
										this.findById('EUComEF_UslugaComplexContainer').hide();
										this.findById('EUComEF_UslugaSprContainer').show();
										base_form.findField('UslugaComplex_id').clearValue();
										base_form.findField('UslugaComplex_id').getStore().removeAll();
										base_form.findField('UslugaComplex_id').fireEvent('change', base_form.findField('UslugaComplex_id'), null);
									}
								}.createDelegate(this),
								params: {
									LpuSection_id: newValue,
									Usluga_date: Ext.util.Format.date(base_form.findField('EvnUslugaCommon_setDate').getValue(), 'd.m.Y')
								}
							});
						} else {
							this.findById('EUComEF_UslugaComplexContainer').hide();
							this.findById('EUComEF_UslugaSprContainer').show();
							base_form.findField('UslugaComplex_id').clearValue();
							base_form.findField('UslugaComplex_id').getStore().removeAll();
							base_form.findField('UslugaComplex_id').fireEvent('change', base_form.findField('UslugaComplex_id'), null);
						}
						break;
/*
					case 'ufa':
						var usluga_combo = base_form.findField('UslugaSpr_id');
						if (!newValue) {
							usluga_combo.setLpuLevelCode(0);
							return false;
						}
						var record = combo.getStore().getById(newValue);
						if (record) {
							usluga_combo.lastQuery = '';
							usluga_combo.getStore().removeAll();
							usluga_combo.setLpuLevelCode(record.get('LpuSectionProfile_Code'));
							usluga_combo.clearValue();
						}
						break;
*/
				}
			}
		}.createDelegate(this));
	},
	keys: [
		{
			alt: true,
			fn: function(inp, e) {
				var current_window = Ext.getCmp('EvnUslugaCommonEditWindow');
				switch (e.getKey()) {
					case Ext.EventObject.C:
						current_window.doSave();
						break;
					case Ext.EventObject.J:
						current_window.onCancelAction();
						break;
					case Ext.EventObject.NUM_ONE:
					case Ext.EventObject.ONE:
						current_window.findById('EUComEF_EvnUslugaPanel').toggleCollapse();
						break;
					case Ext.EventObject.NUM_TWO:
					case Ext.EventObject.TWO:
						current_window.findById('EUComEF_EvnAggPanel').toggleCollapse();
						break;
				}
			}.createDelegate(this),
			key: [
				Ext.EventObject.C,
				Ext.EventObject.J,
				Ext.EventObject.NUM_ONE,
				Ext.EventObject.NUM_TWO,
				Ext.EventObject.ONE,
				Ext.EventObject.TWO
			],
			stopEvent: true
		}
	],
	layout: 'border',
	listeners: {
		'hide': function(win) {
			win.onHide();
		},
		'maximize': function(win) {
			win.findById('EUComEF_EvnAggPanel').doLayout();
			win.findById('EUComEF_EvnUslugaPanel').doLayout();
		},
		'restore': function(win) {
			win.findById('EUComEF_EvnAggPanel').doLayout();
			win.findById('EUComEF_EvnUslugaPanel').doLayout();
		}
	},
	maximizable: true,
	minHeight: 450,
	minWidth: 700,
	modal: true,
	onCancelAction: function() {
		var evn_usluga_id = this.findById('EvnUslugaCommonEditForm').getForm().findField('EvnUslugaCommon_id').getValue();
		if (evn_usluga_id > 0 && this.action == 'add') {
			// удалить услугу
			// закрыть окно после успешного удаления
			var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Удаление услуги..."});
			loadMask.show();
			Ext.Ajax.request({
				callback: function(options, success, response) {
					loadMask.hide();
					if (success) {
						this.hide();
					} else {
						sw.swMsg.alert(lang['oshibka'], lang['pri_udalenii_uslugi_voznikli_oshibki']);
						return false;
					}
				}.createDelegate(this),
				params: {
					'class': 'EvnUslugaCommon',
					'id': evn_usluga_id
				},
				url: '/?c=EvnUsluga&m=deleteEvnUsluga'
			});
		} else {
			this.hide();
		}
	},
	onHide: Ext.emptyFn,
	openEvnAggEditWindow: function(action) {
		if (action != 'add' && action != 'edit' && action != 'view') {
			return false;
		}
		var base_form = this.findById('EvnUslugaCommonEditForm').getForm();
		var grid = this.findById('EUComEF_EvnAggGrid');
		if (this.action == 'view') {
			if (action == 'add') {
				return false;
			} else if (action == 'edit') {
				action = 'view';
			}
		}
		if (getWnd('swEvnAggEditWindow').isVisible()) {
			sw.swMsg.alert(lang['soobschenie'], lang['okno_redaktirovaniya_oslojneniya_uje_otkryito']);
			return false;
		}
		if (action == 'add' && base_form.findField('EvnUslugaCommon_id').getValue() == 0) {
			this.doSave({
				openChildWindow: function() {
					this.openEvnAggEditWindow(action);
				}.createDelegate(this),
				print: false
			});
			return false;
		}
		var params = new Object();
		var formParams = new Object();
		params.action = action;
		params.callback = function(data) {
			if (!data || !data.EvnAggData) {
				return false;
			}
			var record = grid.getStore().getById(data.EvnAggData[0].EvnAgg_id);
			if (!record) {
				if (grid.getStore().getCount() == 1 && !grid.getStore().getAt(0).get('EvnAgg_id')) {
					grid.getStore().removeAll();
				}
				grid.getStore().loadData(data.EvnAggData, true);
			} else {
				var grid_fields = new Array();
				var i = 0;
				grid.getStore().fields.eachKey(function(key, item) {
					grid_fields.push(key);
				});
				for (i = 0; i < grid_fields.length; i++) {
					record.set(grid_fields[i], data.EvnAggData[0][grid_fields[i]]);
				}
				record.commit();
			}
		}.createDelegate(this);
		params.onHide = function() {
			grid.getView().focusRow(0);
			grid.getSelectionModel().selectFirstRow();
		}.createDelegate(this);
		params.Person_id = this.findById('EUComEF_PersonInformationFrame').getFieldValue('Person_id');
		params.Person_Birthday = this.findById('EUComEF_PersonInformationFrame').getFieldValue('Person_Birthday');
		params.Person_Firname = this.findById('EUComEF_PersonInformationFrame').getFieldValue('Person_Firname');
		params.Person_Secname = this.findById('EUComEF_PersonInformationFrame').getFieldValue('Person_Secname');
		params.Person_Surname = this.findById('EUComEF_PersonInformationFrame').getFieldValue('Person_Surname');
		if (action == 'add') {
			formParams.EvnAgg_id = 0;
			formParams.EvnAgg_pid = base_form.findField('EvnUslugaCommon_id').getValue();
			formParams.Person_id = base_form.findField('Person_id').getValue();
			formParams.PersonEvn_id = base_form.findField('PersonEvn_id').getValue();
			formParams.Server_id = base_form.findField('Server_id').getValue();
		} else {
			var selected_record = grid.getSelectionModel().getSelected();
			if (!selected_record || !selected_record.get('EvnAgg_id')) {
				return false;
			}
			if (selected_record.get('accessType') != 'edit') {
				params.action = 'view';
			}
			formParams.EvnAgg_id = selected_record.get('EvnAgg_id');
		}
		params.formParams = formParams;
		getWnd('swEvnAggEditWindow').show(params);
	},
	parentClass: null,
	plain: true,
	resizable: true,
	addEvnUslugaSpecPanel: function (arguments) {
		
		// Специальное лечение для типа услуги Лучевое лечение
		this.EvnUslugaOnkoBeamPanel = new sw.Promed.Panel({
			autoHeight: true,
			style: 'margin-bottom: 0.5em;',
			bodyStyle:'background:#DFE8F6;padding:5px;',
			border: true,
			labelWidth: 320,
			collapsible: true,
			region: 'north',
			layout: 'form',
			title: lang['spetsialnoe_lechenie'],
			items: [{
				xtype: 'panel',
				layout: 'column',
				labelWidth: 220,
				border: false,
				bodyStyle:'background:#DFE8F6;padding:5px;',
				items: [{
						layout: 'form',
						border: false,
						labelWidth: 315,
						width: 420,	
						bodyStyle:'background:#DFE8F6;',	
						items: [{
							fieldLabel: lang['data_nachala_kursa_luchevoy_terapii'],
							name: 'EvnUslugaOnkoBeam_setDT',
							id: 'EUComEF_EvnUslugaOnkoBeam_setDT',
							xtype: 'swdatefield',
							disabled: true,
							tabIndex: TABINDEX_EUCOMEF + 14,
							plugins: [new Ext.ux.InputTextMask('99.99.9999', false)]
						}]
					}, {
						layout: 'form',
						border: false,
						labelWidth: 0,
						width: 200,	
						bodyStyle:'background:#DFE8F6; margin-top: -5px; color: #888',	
						html: lang['-_cootvetstvuet_date_vyipolneniya_uslugi']
				}]
			}, {
				fieldLabel: lang['data_okonchaniya_kursa_luchevoy_terapii'],
				name: 'EvnUslugaOnkoBeam_disDT',
				id: 'EUComEF_EvnUslugaOnkoBeam_disDT',
				xtype: 'swdatefield',
				tabIndex: TABINDEX_EUCOMEF + 15,
				plugins: [new Ext.ux.InputTextMask('99.99.9999', false)]
			}, {
				hiddenName: 'OnkoUslugaBeamIrradiationType_id',
				xtype: 'swonkouslugabeamirradiationtypecombo',
				tabIndex: TABINDEX_EUCOMEF + 16,
				width: 300
			}, {
				hiddenName: 'OnkoUslugaBeamKindType_id',
				xtype: 'swonkouslugabeamkindtypecombo',
				tabIndex: TABINDEX_EUCOMEF + 17,
				width: 300
			}, {
				hiddenName: 'OnkoUslugaBeamMethodType_id',
				xtype: 'swonkouslugabeammethodtypecombo',
				tabIndex: TABINDEX_EUCOMEF + 18,
				width: 300
			}, {
				hiddenName: 'OnkoUslugaBeamRadioModifType_id',
				xtype: 'swonkouslugabeamradiomodiftypecombo',
				tabIndex: TABINDEX_EUCOMEF + 19,
				width: 300
			}, {
				hiddenName: 'OnkoUslugaBeamFocusType_id',
				xtype: 'swonkouslugabeamfocustypecombo',
				tabIndex: TABINDEX_EUCOMEF + 20,
				width: 300
			}, {
				xtype: 'panel',
				layout: 'column',
				labelWidth: 220,
				border: false,
				bodyStyle:'background:#DFE8F6;padding:5px;',
				items: [{
						layout: 'form',
						border: false,
						labelWidth: 220,
						width: 380,	
						bodyStyle:'background:#DFE8F6;',	
						items: [{
							fieldLabel: lang['summarnaya_doza_oblucheniya_opuholi'],
							name: 'EvnUslugaOnkoBeam_TotalDoseTumor',
							xtype: 'textfield',
							tabIndex: TABINDEX_EUCOMEF + 21,
							autoCreate: {tag: "input", maxLength: "8", autocomplete: "off"},
							width: 150
						}, {
							fieldLabel: lang['summarnaya_doza_oblucheniya_zon_regionarnogo_metastazirovaniya'],
							name: 'EvnUslugaOnkoBeam_TotalDoseRegZone',
							xtype: 'textfield',
							tabIndex: TABINDEX_EUCOMEF + 23,
							autoCreate: {tag: "input", maxLength: "8", autocomplete: "off"},
							width: 150
						}]
					}, {
						layout: 'form',
						border: false,
						labelWidth: 120,
						width: 250,	
						bodyStyle:'background:#DFE8F6;',	
						items: [{
							hiddenName: 'OnkoUslugaBeamUnitType_id',
							hideLabel: true,
							xtype: 'swonkouslugabeamunittypecombo',
							value: 1,
							allowBlank: false,
							tabIndex: TABINDEX_EUCOMEF + 22,
							width: 120
						}, {
							hiddenName: 'OnkoUslugaBeamUnitType_did',
							hideLabel: true,
							xtype: 'swonkouslugabeamunittypecombo',
							value: 1,
							allowBlank: false,
							tabIndex: TABINDEX_EUCOMEF + 24,
							width: 120
						}]
				}]
			}]
		});
		
		// Специальное лечение для типа услуги Химиотерапевтическое лечение
		this.EvnUslugaOnkoChemPanel = new sw.Promed.Panel({
			autoHeight: true,
			style: 'margin-bottom: 0.5em;',
			bodyStyle:'background:#DFE8F6;padding:5px;',
			border: true,
			collapsible: true,
			region: 'north',
			layout: 'form',
			labelWidth: 320,
			title: lang['spetsialnoe_lechenie'],
			items: [{
				xtype: 'panel',
				layout: 'column',
				labelWidth: 220,
				border: false,
				bodyStyle:'background:#DFE8F6;padding:5px;',
				items: [{
						layout: 'form',
						border: false,
						labelWidth: 315,
						width: 420,	
						bodyStyle:'background:#DFE8F6;',	
						items: [{
							fieldLabel: lang['data_nachala_himioterapevticheskogo_lecheniya'],
							name: 'EvnUslugaOnkoChem_setDT',
							id: 'EUComEF_EvnUslugaOnkoChem_setDT',
							xtype: 'swdatefield',
							disabled: true,
							plugins: [new Ext.ux.InputTextMask('99.99.9999', false)]
						}]
					}, {
						layout: 'form',
						border: false,
						labelWidth: 0,
						width: 200,	
						bodyStyle:'background:#DFE8F6; margin-top: -5px; color: #888',	
						html: lang['-_cootvetstvuet_date_vyipolneniya_uslugi']
				}]
			}, {
				fieldLabel: lang['data_okonchaniya_himioterapevticheskogo_lecheniya'],
				name: 'EvnUslugaOnkoChem_disDT',
				id: 'EUComEF_EvnUslugaOnkoChem_disDT ',
				xtype: 'swdatefield',
				plugins: [new Ext.ux.InputTextMask('99.99.9999', false)]
			}, {
				hiddenName: 'OnkoUslugaChemKindType_id',
				xtype: 'swonkouslugachemkindtypecombo',
				width: 300
			}, {
				hiddenName: 'OnkoUslugaChemFocusType_id',
				xtype: 'swonkouslugachemfocustypecombo',
				width: 300
			}, {
				id: 'EUComEF_OnkoDrug_id',
				hiddenName: 'OnkoDrug_id',
				xtype: 'swonkodrugcombo',
				width: 300
			}, {
				fieldLabel: lang['doza'],
				name: 'EvnUslugaOnkoChem_Dose',
				xtype: 'textfield',
				autoCreate: {tag: "input", maxLength: "24", autocomplete: "off"},
				width: 150
			}]
		});
		
		// Специальное лечение для типа услуги Гормоноиммунотерапевтическое лечение
		this.EvnUslugaOnkoGormunPanel = new sw.Promed.Panel({
			autoHeight: true,
			style: 'margin-bottom: 0.5em;',
			bodyStyle:'background:#DFE8F6;padding:5px;',
			border: true,
			collapsible: true,
			region: 'north',
			layout: 'form',
			labelWidth: 240,
			title: lang['spetsialnoe_lechenie'],
			items: [{
				xtype: 'panel',
				layout: 'column',
				border: false,
				bodyStyle:'background:#DFE8F6;padding:5px;',
				items: [{
						layout: 'form',
						border: false,
						labelWidth: 235,
						width: 350,	
						bodyStyle:'background:#DFE8F6;',	
						items: [{
							fieldLabel: lang['data_nachala_kursa_gormonoimmunoterapii'],
							name: 'EvnUslugaOnkoGormun_setDT',
							id: 'EUComEF_EvnUslugaOnkoGormun_setDT',
							xtype: 'swdatefield',
							disabled: true,
							plugins: [new Ext.ux.InputTextMask('99.99.9999', false)]
						}]
					}, {
						layout: 'form',
						border: false,
						labelWidth: 0,
						width: 250,	
						bodyStyle:'background:#DFE8F6; margin-top: 2px; color: #888',	
						html: lang['-_cootvetstvuet_date_vyipolneniya_uslugi']
				}]
			}, {
				fieldLabel: lang['data_okonchaniya_kursa_gormonoimmunoterapii'],
				name: 'EvnUslugaOnkoGormun_disDT',
				id: 'EUComEF_EvnUslugaOnkoGormun_disDT',
				xtype: 'swdatefield',
				plugins: [new Ext.ux.InputTextMask('99.99.9999', false)]
			}, {
				hiddenName: 'OnkoGormunType_id',
				xtype: 'swonkogormuntypecombo',
				width: 300
			}, {
				hiddenName: 'OnkoUslugaGormunFocusType_id',
				xtype: 'swonkouslugagormunfocustypecombo',
				width: 300
			}, {
				id: 'EUComEF_OnkoDrug_id',
				hiddenName: 'OnkoDrug_id',
				xtype: 'swonkodrugcombo',
				width: 300
			}, {
				fieldLabel: lang['doza'],
				name: 'EvnUslugaOnkoGormun_Dose',
				xtype: 'textfield',
				autoCreate: {tag: "input", maxLength: "24", autocomplete: "off"},
				width: 150
			}]
		});
		
		var base_form = this.findById('EvnUslugaCommonEditForm').getForm();
		var delFieldsRecursive = function(item) {
			if (item.items) {
				item.items.each(delFieldsRecursive);
			}
			else if (item.xtype) {
				base_form.remove(item);
			}
		}
		
		this.findById('EUComEF_EvnUslugaOnkoPanel').items.each(delFieldsRecursive);
		this.findById('EUComEF_EvnUslugaOnkoPanel').removeAll(true);

		var usluga_id = null;
		
		if (arguments[0].formParams.Usluga_id) {
			usluga_id = arguments[0].formParams.Usluga_id;
		} 

		if (arguments[0].EvnClass_SysNick){
			this.EvnClass_SysNick = arguments[0].EvnClass_SysNick;
		} else {
			this.EvnClass_SysNick = 'EvnUslugaCommon';
		}

		if (usluga_id == 1012820 || this.EvnClass_SysNick == 'EvnUslugaOnkoBeam') {
			
			this.findById('EUComEF_EvnUslugaOnkoPanel').add(this.EvnUslugaOnkoBeamPanel);
			
		} else if (usluga_id == 1012821 || this.EvnClass_SysNick == 'EvnUslugaOnkoChem') {
			
			this.findById('EUComEF_EvnUslugaOnkoPanel').add(this.EvnUslugaOnkoChemPanel);
			this.findById('EUComEF_OnkoDrug_id').getStore().load({
				params: {where: 'where OnkoDrugType_id = 1 and OnkoDrug_pid <> ""'}
			});
			
		} else if (usluga_id == 1012822 || this.EvnClass_SysNick == 'EvnUslugaOnkoGormun') {
			
			this.findById('EUComEF_EvnUslugaOnkoPanel').add(this.EvnUslugaOnkoGormunPanel);
			this.findById('EUComEF_OnkoDrug_id').getStore().load({
				params: {where: 'where OnkoDrugType_id = 2 and OnkoDrug_pid <> ""'}
			});
			
		}	
		
	},
	show: function() {
        //log(arguments);
		this.addEvnUslugaSpecPanel(arguments);

		sw.Promed.swEvnUslugaCommonEditWindow.superclass.show.apply(this, arguments);

		this.findById('EUComEF_EvnAggPanel').collapse();
		this.findById('EUComEF_EvnUslugaPanel').expand();

		// По умолчанию доступен только комбо с общим справочником услуг
		this.findById('EUComEF_UslugaComplexContainer').hide();
		this.findById('EUComEF_UslugaSprContainer').show();

		this.restore();
		this.center();

		var base_form = this.findById('EvnUslugaCommonEditForm').getForm();
		base_form.reset();

		this.action = null;
		this.callback = Ext.emptyFn;
		this.onHide = Ext.emptyFn;
		this.parentClass = null;

		this.findById('EUComEF_EvnAggGrid').getStore().removeAll();
		base_form.findField('EvnUslugaCommon_pid').getStore().removeAll();
		base_form.findField('Lpu_uid').enable();
		base_form.findField('Org_uid').disable();
		base_form.findField('Lpu_uid').disable();
		base_form.findField('LpuSection_uid').disable();
		base_form.findField('MedStaffFact_id').disable();
		base_form.findField('Org_uid').disable();

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

		if (arguments[0].parentClass) {
			this.parentClass = arguments[0].parentClass;
		}

		//https://redmine.swan.perm.ru/issues/12875
		if (this.parentClass == 'EvnVizit')
		base_form.findField('EvnUslugaCommon_pid').setFieldLabel(lang['poseschenie']);
		if (this.parentClass == 'EvnPS')
			base_form.findField('EvnUslugaCommon_pid').setFieldLabel(lang['dvijenie']);


		if (arguments[0].parentEvnComboData) {
			base_form.findField('EvnUslugaCommon_pid').getStore().loadData(arguments[0].parentEvnComboData);
		}

		if (arguments[0].doSave){
			this.doSave = arguments[0].doSave;
		} else {
			this.doSave = this.doSave_default;
		}

		if (arguments[0].formUrl){
			this.findById('EvnUslugaCommonEditForm').getForm().url = arguments[0].formUrl;
		} else {
			this.findById('EvnUslugaCommonEditForm').getForm().url = this.findById('EvnUslugaCommonEditForm').getForm().default_url;
		}

		if (arguments[0].formLoadUrl){
			this.findById('EvnUslugaCommonEditForm').getForm().formLoadUrl = arguments[0].formLoadUrl;
		} else {
			this.findById('EvnUslugaCommonEditForm').getForm().formLoadUrl = this.findById('EvnUslugaCommonEditForm').getForm().default_formLoadUrl;
		}

		if (arguments[0].EvnClass_SysNick){
			this.EvnClass_SysNick = arguments[0].EvnClass_SysNick;
		} else {
			this.EvnClass_SysNick = 'EvnUslugaCommon';
		}
		
		if (arguments[0].UslugaConfirm){
			this.UslugaConfirm = arguments[0].UslugaConfirm;
		} else {
			this.UslugaConfirm = null;
		}
		
		/*
		 // определенный медстафффакт
		 if ( arguments[0].UserMedStaffFact_id && arguments[0].UserMedStaffFact_id > 0 )
		 {
		 this.UserMedStaffFact_id = arguments[0].UserMedStaffFact_id;
		 }
		 else
		 {
		 this.UserMedStaffFact_id = null;
		 // если в настройках есть medstafffact, то имеем список мест работы
		 if ( Ext.globalOptions.globals['medstafffact'] && Ext.globalOptions.globals['medstafffact'].length > 0 )
		 {
		 this.UserMedStaffFacts = Ext.globalOptions.globals['medstafffact'];
		 this.UserLpuSections = Ext.globalOptions.globals['lpusection'];
		 }
		 else
		 {
		 // свободный выбор врача и отделения
		 this.UserMedStaffFacts = null;
		 this.UserLpuSections = null;
		 }
		 }

		 // определенный LpuSection
		 if ( arguments[0].UserLpuSection_id && arguments[0].UserLpuSection_id > 0 )
		 {
		 this.UserLpuSection_id = arguments[0].UserLpuSection_id;
		 }
		 else
		 {
		 this.UserLpuSection_id = null;
		 // если в настройках есть lpusection, то имеем список мест работы
		 if ( Ext.globalOptions.globals['lpusection'] && Ext.globalOptions.globals['lpusection'].length > 0 )
		 {
		 this.UserLpuSections = Ext.globalOptions.globals['lpusection'];
		 }
		 else
		 {
		 // свободный выбор врача и отделения
		 this.UserLpuSectons = null;
		 }
		 }
		 */
		if (this.action == 'add') {
			this.findById('EUComEF_EvnAggPanel').isLoaded = true;
			this.findById('EUComEF_EvnUslugaPanel').isLoaded = true;
		} else {
			this.findById('EUComEF_EvnAggPanel').isLoaded = false;
			this.findById('EUComEF_EvnUslugaPanel').isLoaded = false;
		}
		base_form.setValues(arguments[0].formParams);

		this.findById('EUComEF_PersonInformationFrame').load({
			Person_id: (arguments[0].Person_id ? arguments[0].Person_id : ''),
			Person_Birthday: (arguments[0].Person_Birthday ? arguments[0].Person_Birthday : ''),
			Person_Firname: (arguments[0].Person_Firname ? arguments[0].Person_Firname : ''),
			Person_Secname: (arguments[0].Person_Secname ? arguments[0].Person_Secname : ''),
			Person_Surname: (arguments[0].Person_Surname ? arguments[0].Person_Surname : ''),
			callback: function() {
				var field = base_form.findField('EvnUslugaCommon_setDate');
				clearDateAfterPersonDeath('personpanelid', 'EUComEF_PersonInformationFrame', field);
			}.createDelegate(this)
		});

		var evn_combo = base_form.findField('EvnUslugaCommon_pid');
		var lpu_combo = base_form.findField('Lpu_uid');
		var lpu_section_combo = base_form.findField('LpuSection_uid');
		var med_personal_combo = base_form.findField('MedStaffFact_id');
		var org_combo = base_form.findField('Org_uid');
		var usluga_place_combo = base_form.findField('UslugaPlace_id');
		var usluga_section_combo = base_form.findField('UslugaComplex_id');
		var usluga_spr_combo = base_form.findField('UslugaSpr_id');
		// log(base_form.findField('Usluga_id').getValue());
		var loadMask = new Ext.LoadMask(this.getEl(), {msg: LOAD_WAIT});
		loadMask.show();
		this.findById('EUComEF_EvnAggGrid').getStore().removeAll();
		this.findById('EUComEF_EvnAggGrid').getTopToolbar().items.items[0].enable();
		this.findById('EUComEF_EvnAggGrid').getTopToolbar().items.items[1].disable();
		this.findById('EUComEF_EvnAggGrid').getTopToolbar().items.items[2].disable();
		this.findById('EUComEF_EvnAggGrid').getTopToolbar().items.items[3].disable();
		var evn_usluga_common_pid = null;
		var enable_usluga_section_load = getUslugaOptions().enable_usluga_section_load;
		
		base_form.findField('EvnClass_SysNick').setValue(this.EvnClass_SysNick);
		
		usluga_spr_combo.lastQuery = '';
		usluga_spr_combo.allowedCodeList = null;

		this.EvnXmlPanel.doReset();
		this.EvnXmlPanel.collapse();
		this.EvnXmlPanel.LpuSectionField = lpu_section_combo;
		this.EvnXmlPanel.MedStaffFactField = med_personal_combo;
		
		if (getGlobalOptions().region && getGlobalOptions().region.nick == 'ufa' && this.parentClass=='EvnPS' ) {
			lpu_section_combo.disableLinkedElements();
			med_personal_combo.disableParentElement();
		}
		else {
			lpu_section_combo.enableLinkedElements();
			med_personal_combo.enableParentElement();
		}
		// Лабораторные подтверждения
		if ( this.UslugaConfirm == 'LabConfirm'){
			usluga_spr_combo.allowedCodeList = "'02110140', '02110141', '02110142', '02110143', '02110144', '02110145', '02110146', '02110147', '02110148', '02110149', '02110150', '02110151', '02110171', '02110172', '02110173', '02110174', '04231108', '04231131', '02240132', '04280121'";
		}
		
		// Инструментальные подтверждения
		if ( this.UslugaConfirm == 'FuncConfirm'){
			usluga_spr_combo.allowedCodeList = "'02270203', '02001301', '02240114', '02240116', '02240117', '02240118', '02240120'";
		}

		switch (this.action) {
			case 'add':
				this.setTitle(WND_POL_EUCOMADD);
				this.enableEdit(true);
				// Для Уфы проставляем количество = 1
				// и вид оплаты высталяем по умолчанию ОМС (http://172.19.61.24:85/issues/show/2090)
				// хорошо бы конечно помечать поля заполненные по умолчанию каким нибудь цветом в рамках всего проекта...
				// if ( getGlobalOptions().region && getGlobalOptions().region.nick == 'ufa' ) {
				base_form.findField('EvnUslugaCommon_Kolvo').setValue(1);

				var PayType_SysNick = 'oms';
				switch ( getRegionNick() ) {
					case 'by': PayType_SysNick = 'besus'; break;
					case 'kz': PayType_SysNick = 'Resp'; break;
				}
				base_form.findField('PayType_id').setFieldValue('PayType_SysNick', PayType_SysNick);
				// }

				LoadEmptyRow(this.findById('EUComEF_EvnAggGrid'));

				var lpu_section_id;
				var set_date = false;
				var usluga_id = base_form.findField('Usluga_id').getValue();

				if ( evn_combo.getStore().getCount() > 0 /*&& (this.parentClass == 'EvnPLStom' || this.parentClass == 'EvnVizit')*/ ) {
					evn_combo.setValue(evn_combo.getStore().getAt(0).get('Evn_id'));

					lpu_section_id = evn_combo.getStore().getAt(0).get('LpuSection_id');
				}
				else {
					set_date = true;
				}

				if ( null != this.parentClass && this.parentClass.inlist([ 'EvnPLStom', 'EvnVizit', 'EvnPrescr' ]) ) {
					evn_combo.disable();
				}

				setCurrentDateTime({
					callback: function() {
						if ( set_date ) {
							base_form.findField('EvnUslugaCommon_setDate').fireEvent('change', base_form.findField('EvnUslugaCommon_setDate'), base_form.findField('EvnUslugaCommon_setDate').getValue());
						}
					},
					dateField: base_form.findField('EvnUslugaCommon_setDate'),
					loadMask: false,
					setDate: set_date,
					setDateMaxValue: true,
					setDateMinValue: false,
					setTime: false,
					timeField: base_form.findField('EvnUslugaCommon_setTime'),
					windowId: this.id
				});

				base_form.findField('EvnUslugaCommon_setDate').fireEvent('change', base_form.findField('EvnUslugaCommon_setDate'), base_form.findField('EvnUslugaCommon_setDate').getValue());
				usluga_place_combo.fireEvent('change', usluga_place_combo, null);

				if ( this.parentClass == 'EvnPrescr' ) {
					// base_form.findField('EvnUslugaCommon_setDate').disable();
					// base_form.findField('UslugaPlace_id').disable();
					// base_form.findField('LpuSection_uid').disable();
					usluga_spr_combo.disable();

					if ( lpu_section_id ) {
						usluga_place_combo.setValue(1);
						usluga_place_combo.fireEvent('change', usluga_place_combo, 1);

						var index = lpu_section_combo.getStore().findBy(function(rec) {
							if ( rec.get('LpuSection_id') == lpu_section_id ) {
								return true;
							}
							else {
								return false;
							}
						});

						if ( index >= 0 ) {
							lpu_section_combo.setValue(lpu_section_id);
							lpu_section_combo.fireEvent('change', lpu_section_combo, lpu_section_id);
						}
					}
				}
				else {
					if ( this.parentClass != 'EvnPLStom' )
					{
						if (evn_combo.getStore().getAt(0)) {
							evn_combo.fireEvent('change', evn_combo, evn_combo.getStore().getAt(0).get('Evn_id'), 0);
						}
					}
					// Включаем фильтр услуг по коду уровня ЛПУ
					usluga_spr_combo.setLpuLevelCodeFilterEnabled(false);
				}
				
				if ( usluga_id > 0 ) {
					usluga_spr_combo.getStore().load({
						callback: function() {
							usluga_spr_combo.getStore().each(function(record) {
								if ( record.get('Usluga_id') == usluga_id ) {
									usluga_spr_combo.setValue(usluga_id);
									usluga_spr_combo.fireEvent('select', usluga_spr_combo, record, 0);
								}
							});
							// Включаем фильтр услуг по коду уровня ЛПУ
							usluga_spr_combo.setLpuLevelCodeFilterEnabled(true);
						},
						params: {where: "where UslugaType_id = 2 and Usluga_id = " + usluga_id}
					});
					
					var OnkoUslugaList = '1012820, 1012821, 1012822';
					if ( OnkoUslugaList.indexOf(usluga_id)>=0 ) {
						usluga_spr_combo.disable();
					}
				}

				if ( !evn_combo.disabled ) {
					evn_combo.focus(true, 250);
				}
				else if ( !base_form.findField('EvnUslugaCommon_setDate').disabled ) {
					base_form.findField('EvnUslugaCommon_setDate').focus(true, 250);
				}
				else {
					base_form.findField('EvnUslugaCommon_setTime').focus(true, 250);
				}

				if ( !base_form.findField('MedStaffFact_id').disabled && arguments[0].formParams.MedStaffFact_id ) {
					base_form.findField('MedStaffFact_id').setValue(arguments[0].formParams.MedStaffFact_id);
				}
				
				loadMask.hide();

				base_form.clearInvalid();
			break;

			case 'edit':
			case 'view':
				
				var EvnClass = null;
				var formFieldTypes = [ 'panel', 'fieldset' ];
				var addFieldsRecursive = function(item) {
					if (item.items) {
						item.items.each(addFieldsRecursive);
					}
					else if (item.xtype && !item.xtype.toString().inlist(formFieldTypes)) {
						base_form.add(item);
					}
				}
				var disableFieldsRecursive = function(item) {
					if (item.items) {
						item.items.each(disableFieldsRecursive);
					}
					else if (item.xtype && !item.xtype.toString().inlist(formFieldTypes)) {
						item.disable();
					}
				}
				
				switch (this.EvnClass_SysNick) {
					
					case 'EvnUslugaOnkoBeam':
						EvnClass = 'EvnUslugaOnkoBeam';
						this.EvnUslugaOnkoBeamPanel.items.each(addFieldsRecursive);
						break;
					
					case 'EvnUslugaOnkoChem':
						EvnClass = 'EvnUslugaOnkoChem';
						this.EvnUslugaOnkoChemPanel.items.each(addFieldsRecursive);
						break;
					
					case 'EvnUslugaOnkoGormun':
						EvnClass = 'EvnUslugaOnkoGormun';
						this.EvnUslugaOnkoGormunPanel.items.each(addFieldsRecursive);
						break;
						
					default:
						EvnClass = 'EvnUslugaCommon';
						break;
				}
				
				var evn_usluga_common_id = base_form.findField('EvnUslugaCommon_id').getValue();
				if (!evn_usluga_common_id) {
					loadMask.hide();
					this.hide();
					return false;
				}
				base_form.load({
					failure: function() {
						loadMask.hide();
						sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_zagruzke_dannyih_formyi'], function() {
							this.hide();
						}.createDelegate(this));
					}.createDelegate(this),
					params: {
						'class': EvnClass,
						'id': evn_usluga_common_id
					},
					success: function() {
						// В зависимости от accessType переопределяем this.action
						if (base_form.findField('accessType').getValue() == 'view') {
							this.action = 'view';
						}
						if (this.action == 'edit') {
							this.setTitle(WND_POL_EUCOMEDIT);
							this.enableEdit(true);
						} else {
							this.setTitle(WND_POL_EUCOMVIEW);
							this.enableEdit(false);
							this.findById('EUComEF_EvnUslugaOnkoPanel').items.each(disableFieldsRecursive);
						}

						if (this.action == 'edit') {
							setCurrentDateTime({
								dateField: base_form.findField('EvnUslugaCommon_setDate'),
								loadMask: false,
								setDate: false,
								setDateMaxValue: true,
								windowId: this.id
							});
						} else {
							this.findById('EUComEF_EvnAggGrid').getTopToolbar().items.items[0].disable();
						}

						if ( base_form.findField('EvnUslugaCommon_id').getValue() > 0) {
							this.EvnXmlPanel.setReadOnly('view' == this.action);
							this.EvnXmlPanel.setBaseParams({
								userMedStaffFact: sw.Promed.MedStaffFactByUser.last,
								UslugaComplex_id: usluga_combo.getValue(),
								Server_id: base_form.findField('Server_id').getValue(),
								Evn_id: base_form.findField('EvnUslugaCommon_id').getValue()
							});
							this.EvnXmlPanel.doLoadData();
						}

						var evn_usluga_common_pid = evn_combo.getValue();
						var lpu_uid = lpu_combo.getValue();
						var lpu_section_pid;
						var lpu_section_uid = lpu_section_combo.getValue();
						var med_personal_id = base_form.findField('MedPersonal_id').getValue();
						var org_uid = org_combo.getValue();
						var record;
						var usluga_id = base_form.findField('Usluga_id').getValue();
						var usluga_place_id = usluga_place_combo.getValue();
						var index = evn_combo.getStore().findBy(function(rec) {
							if (rec.get('Evn_id') == evn_usluga_common_pid) {
								return true;
							} else {
								return false;
							}
						});
						record = evn_combo.getStore().getAt(index);
						if (record) {
							evn_combo.setValue(evn_usluga_common_pid);
						} else {
							evn_combo.clearValue();
						}
						if (usluga_place_id) {
							if (this.action == 'edit') {
								usluga_place_combo.fireEvent('change', usluga_place_combo, usluga_place_id, -1);
							}
							record = usluga_place_combo.getStore().getById(usluga_place_id);
							if (!record) {
								loadMask.hide();
								return false;
							}
							switch (Number(record.get('UslugaPlace_Code'))) {
								case 1:
									var section_filter_params = {};
									var medstafffact_filter_params = {};
									var user_med_staff_fact_id = this.UserMedStaffFact_id;
									var user_lpu_section_id = this.UserLpuSection_id;
									var user_med_staff_facts = this.UserMedStaffFacts;
									var user_lpu_sections = this.UserLpuSections;
									section_filter_params.allowLowLevel = 'yes';
									section_filter_params.onDate = Ext.util.Format.date(base_form.findField('EvnUslugaCommon_setDate').getValue(), 'd.m.Y');
									medstafffact_filter_params.allowLowLevel = 'yes';
									medstafffact_filter_params.onDate = Ext.util.Format.date(base_form.findField('EvnUslugaCommon_setDate').getValue(), 'd.m.Y');
									// фильтр или на конкретное место работы или на список мест работы
									if (user_med_staff_fact_id && user_lpu_section_id && this.action == 'add') {
										section_filter_params.id = user_lpu_section_id;
										medstafffact_filter_params.id = user_med_staff_fact_id;
									} else if (user_med_staff_facts && user_lpu_sections && this.action == 'add') {
										section_filter_params.ids = user_lpu_sections;
										medstafffact_filter_params.ids = user_med_staff_facts;
									}
									setLpuSectionGlobalStoreFilter(section_filter_params);
									setMedStaffFactGlobalStoreFilter(medstafffact_filter_params);
									// Отделение
									base_form.findField('LpuSection_uid').getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));
									base_form.findField('MedStaffFact_id').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));
									index = base_form.findField('LpuSection_uid').getStore().findBy(function(rec, id) {
										if (rec.get('LpuSection_id') == lpu_section_uid) {
											return true;
										} else {
											return false;
										}
									}.createDelegate(this));
									record = base_form.findField('LpuSection_uid').getStore().getAt(index);
									if (record) {
										lpu_section_combo.setValue(lpu_section_uid);
										lpu_section_pid = record.get('LpuSection_pid');
									} else {
										lpu_section_uid = 0;
									}
									index = med_personal_combo.getStore().findBy(function(rec) {
										if (rec.get('LpuSection_id').inlist([ lpu_section_uid, lpu_section_pid ]) && rec.get('MedPersonal_id') == med_personal_id) {
											return true;
										} else {
											return false;
										}
									});
									record = med_personal_combo.getStore().getAt(index);
									if (record) {
										med_personal_combo.setValue(record.get('MedStaffFact_id'));
									}
									/**
									 *	если форма открыта на редактирование и задано отделение и
									 *	место работы или задан список мест работы, то не даем редактировать вообще
									 */
									if (( user_med_staff_fact_id && user_lpu_section_id ) || ( this.UserMedStaffFacts && this.UserMedStaffFacts.length > 0 )) {
										base_form.findField('LpuSection_uid').disable();
										base_form.findField('MedStaffFact_id').disable();
									}
									if (getGlobalOptions().region && getGlobalOptions().region.nick == 'perm' && enable_usluga_section_load) {
										base_form.findField('UslugaComplex_id').getStore().load({
											callback: function() {
												var usluga_section_record = false;
												if (base_form.findField('UslugaComplex_id').getStore().getCount() > 0) {
													base_form.findField('UslugaComplex_id').getStore().each(function(rec) {
														if (rec.get('Usluga_id') == usluga_id) {
															this.findById('EUComEF_UslugaComplexContainer').show();
															this.findById('EUComEF_UslugaSprContainer').hide();
															usluga_section_record = true;
															base_form.findField('UslugaComplex_id').setValue(rec.get('UslugaComplex_id'));
															base_form.findField('UslugaComplex_id').fireEvent('select', base_form.findField('UslugaComplex_id'), rec, 0);
														}
													}.createDelegate(this));
												}
												if (usluga_section_record == false && usluga_id) {
													usluga_spr_combo.getStore().load({
														callback: function() {
															usluga_spr_combo.getStore().each(function(record) {
																if (record.get('Usluga_id') == usluga_id) {
																	usluga_spr_combo.setValue(usluga_id);
																	usluga_spr_combo.fireEvent('select', usluga_spr_combo, record, 0);
																}
															});
															// Включаем фильтр услуг по коду уровня ЛПУ
															usluga_spr_combo.setLpuLevelCodeFilterEnabled(true);
														},
														params: {where: "where UslugaType_id = 2 and Usluga_id = " + usluga_id}
													});
												}
											}.createDelegate(this),
											params: {
												LpuSection_id: lpu_section_uid,
												Usluga_date: Ext.util.Format.date(base_form.findField('EvnUslugaCommon_setDate').getValue(), 'd.m.Y')
											}
										});
									} else {
										// Выключаем фильтр услуг по коду уровня ЛПУ
										usluga_spr_combo.setLpuLevelCodeFilterEnabled(false);
										if (usluga_id) {
											usluga_spr_combo.getStore().load({
												callback: function() {
													usluga_spr_combo.getStore().each(function(record) {
														if (record.get('Usluga_id') == usluga_id) {
															usluga_spr_combo.setValue(usluga_id);
															usluga_spr_combo.fireEvent('select', usluga_spr_combo, record, 0);
														}
													});
													// Включаем фильтр услуг по коду уровня ЛПУ
													base_form.findField('EvnUslugaCommon_setDate').fireEvent('change', base_form.findField('EvnUslugaCommon_setDate'), base_form.findField('EvnUslugaCommon_setDate').getValue());
													usluga_spr_combo.setLpuLevelCodeFilterEnabled(true);
												},
												params: {where: "where UslugaType_id = 2 and Usluga_id = " + usluga_id}
											});
										} else {
											// Включаем фильтр услуг по коду уровня ЛПУ
											usluga_spr_combo.setLpuLevelCodeFilterEnabled(true);
										}
									}
									break;
								case 2:
									// Другое ЛПУ
									lpu_combo.getStore().load({
										callback: function(records, options, success) {
											if (success) {
												lpu_combo.setValue(lpu_uid);
											}
										},
										params: {
											Org_id: lpu_uid,
											OrgType: 'lpu'
										}
									});
									// Выключаем фильтр услуг по коду уровня ЛПУ
									usluga_spr_combo.setLpuLevelCodeFilterEnabled(false);
									if (usluga_id) {
										usluga_spr_combo.getStore().load({
											callback: function() {
												usluga_spr_combo.getStore().each(function(record) {
													if (record.get('Usluga_id') == usluga_id) {
														usluga_spr_combo.setValue(usluga_id);
														usluga_spr_combo.fireEvent('select', usluga_spr_combo, record, 0);
													}
												});
												// Включаем фильтр услуг по коду уровня ЛПУ
												usluga_spr_combo.setLpuLevelCodeFilterEnabled(true);
											},
											params: {where: "where UslugaType_id = 2 and Usluga_id = " + usluga_id}
										});
									} else {
										// Включаем фильтр услуг по коду уровня ЛПУ
										usluga_spr_combo.setLpuLevelCodeFilterEnabled(true);
									}
									break;
								case 3:
									// Другая организация
									org_combo.getStore().load({
										callback: function(records, options, success) {
											if (success) {
												org_combo.setValue(org_uid);
											}
										},
										params: {
											Org_id: org_uid,
											OrgType: 'org'
										}
									});
									// Выключаем фильтр услуг по коду уровня ЛПУ
									usluga_spr_combo.setLpuLevelCodeFilterEnabled(false);
									if (usluga_id) {
										usluga_spr_combo.getStore().load({
											callback: function() {
												usluga_spr_combo.getStore().each(function(record) {
													if (record.get('Usluga_id') == usluga_id) {
														usluga_spr_combo.setValue(usluga_id);
														usluga_spr_combo.fireEvent('select', usluga_spr_combo, record, 0);
													}
												});
												// Включаем фильтр услуг по коду уровня ЛПУ
												usluga_spr_combo.setLpuLevelCodeFilterEnabled(true);
											},
											params: {where: "where UslugaType_id = 2 and Usluga_id = " + usluga_id}
										});
									} else {
										// Включаем фильтр услуг по коду уровня ЛПУ
										usluga_spr_combo.setLpuLevelCodeFilterEnabled(true);
									}
									break;
								default:
									loadMask.hide();
									return false;
									break;
							}
						}
						if (this.action == 'edit') {
							if (this.parentClass != null || (evn_usluga_common_pid != null && evn_usluga_common_pid.toString().length > 0)) {
								evn_combo.disable();
								if ( '1012820, 1012821, 1012822'.indexOf(usluga_id)>=0 ) {
									usluga_spr_combo.disable();
								}
								base_form.findField('EvnUslugaCommon_setDate').fireEvent('change', base_form.findField('EvnUslugaCommon_setDate'), base_form.findField('EvnUslugaCommon_setDate').getValue());
								base_form.findField('EvnUslugaCommon_setDate').focus(true, 250);
							} else {
								evn_combo.focus(true, 250);
							}
						} else {
							this.buttons[this.buttons.length - 1].focus();
						}
						loadMask.hide();
						base_form.clearInvalid();
					}.createDelegate(this),
					url: this.findById('EvnUslugaCommonEditForm').getForm().formLoadUrl
				});
				break;
			default:
				loadMask.hide();
				this.hide();
				break;
		}		
	},
	width: 700
});
