/**
* swEvnUslugaDispOrp13EditWindow - окно редактирования/добавления выполнения лабораторного исследования по диспасеризации детей сирот детей-сирот
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package        Polka
* @access         public
* @copyright      Copyright (c) 2009 Swan Ltd.
* @author         Ivan Petukhov aka Lich (megatherion@list.ru)
* @originalauthor Stas Bykov aka Savage (savage1981@gmail.com)
* @version        май 2010
* @comment        Префикс для id компонентов eudo13ew (swEvnUslugaDispOrp13EditWindow)
*	                tabIndex: TABINDEX_EUDO13EF = 9300
*
*
* Использует: окно редактирования талона по диспасеризации детей сирот (swEvnPLDispOrpEditWindow)
*/

sw.Promed.swEvnUslugaDispOrp13EditWindow = Ext.extend(sw.Promed.BaseForm, {
	action: null,
	autoHeight: true,
	buttonAlign: 'left',
    callback: Ext.emptyFn,
    closable: true,
    closeAction: 'hide',
    collapsible: true,
	doSave: function(options) {
		if ( typeof options != 'object' ) {
			options = new Object();
		}

		var add_flag = true;
		var current_window = this;
		var base_form = this.findById('EvnUslugaDispOrp13EditForm').getForm();
		var index = -1;
		var lpu_section_id = current_window.findById('eudo13ewLpuSectionCombo').getValue();
		var lpu_section_name = '';
		var med_staff_fact_id = current_window.findById('eudo13ewMedPersonalCombo').getValue();
		var med_personal_fio = '';
		var uslugacomplex_id = current_window.findById('eudo13ewUslugaComplexCombo').getValue();
		var uslugacomplex_code = '';
		var uslugacomplex_name = '';
		var examination_place_id = current_window.findById('eudo13ewExaminationPlaceCombo').getValue();
		var record_status = current_window.findById('eudo13ewRecord_Status').getValue();

		// Проверка на наличие у врача кода ДЛО или специальности https://redmine.swan.perm.ru/issues/47172
		// Проверку кода ДЛО убрали в https://redmine.swan.perm.ru/issues/118763
		if ( getRegionNick().inlist([ 'kareliya', 'penza' ]) ) {
			var
				MedSpecOms_id = base_form.findField('MedStaffFact_id').getFieldValue('MedSpecOms_id');

			if ( Ext.isEmpty(MedSpecOms_id) ) {
				sw.swMsg.alert(lang['soobschenie'], lang['u_vracha_ne_ukazana_spetsialnost'], function() { base_form.findField('MedStaffFact_id').clearValue(); } );
				return false;
			}
		}

		if (!current_window.findById('EvnUslugaDispOrp13EditForm').getForm().isValid())
		{
            Ext.MessageBox.show({
                buttons: Ext.Msg.OK,
                fn: function() {
                	current_window.findById('eudo13ewEvnUslugaDispOrp_setDate').focus(false);
                },
                icon: Ext.Msg.WARNING,
                msg: ERR_INVFIELDS_MSG,
                title: ERR_INVFIELDS_TIT
            });
            return false;
		}

		if(getRegionNick() != 'kz' && getGlobalOptions().disp_control > 1) // Если выбрано предупреждение или запрет
		{
			var fields_list = "";

			if(Ext.isEmpty(base_form.findField('EvnUslugaDispOrp_Result').getValue())) {
				fields_list += 'Результат <br>';
			}

			if(fields_list.length > 0)
			{
				if(getGlobalOptions().disp_control == 2 && !options.ignoreEmptyFields)
				{
					sw.swMsg.show({
						buttons: Ext.Msg.YESNO,
						fn: function ( buttonId ) {
							if ( buttonId == 'yes' )
							{
								options.ignoreEmptyFields = true;
								current_window.doSave(options);
							}
							else
							{
								return false;
							}
						},
						msg: 'Внимание! Не заполнены поля, обязательные при экспорте на федеральный портал: <br>' + fields_list + '<br> Сохранить?',
						title: 'Предупреждение'
					});
					return false;
				}
				if(getGlobalOptions().disp_control == 3)
				{
					sw.swMsg.alert('Ошибка', 'Не заполнены поля, обязательные при экспорте на федеральный портал: <br>' + fields_list);
					return false;
				}
			}
		}
		
		if ( current_window.findById('eudo13ewEvnUslugaDispOrp_setDate').getValue() > current_window.findById('eudo13ewEvnUslugaDispOrp_didDate').getValue() )
		{
            Ext.MessageBox.show({
                buttons: Ext.Msg.OK,
                fn: function() {
                	current_window.findById('eudo13ewEvnUslugaDispOrp_setDate').focus(false);
                },
                icon: Ext.Msg.WARNING,
                msg: lang['data_issledovaniya_ne_mojet_prevyishat_datu_polucheniya_rezultata'],
                title: lang['oshibka']
            });
            return false;
		}

		var set_date = base_form.findField('EvnUslugaDispOrp_setDate').getValue();
		var set_time = base_form.findField('EvnUslugaDispOrp_setTime').getValue();
		var dis_date = base_form.findField('EvnUslugaDispOrp_disDate').getValue();
		var dis_time = base_form.findField('EvnUslugaDispOrp_disTime').getValue();
		var did_date = base_form.findField('EvnUslugaDispOrp_didDate').getValue();

		if (!Ext.isEmpty(dis_date)) {
			var setDateStr = Ext.util.Format.date(set_date, 'Y-m-d')+' '+(Ext.isEmpty(set_time)?'00:00':set_time);
			var disDateStr = Ext.util.Format.date(dis_date, 'Y-m-d')+' '+(Ext.isEmpty(dis_time)?'00:00':dis_time);

			if (Date.parseDate(setDateStr, 'Y-m-d H:i') > Date.parseDate(disDateStr, 'Y-m-d H:i')) {
				Ext.MessageBox.show({
					buttons: Ext.Msg.OK,
					fn: function() {base_form.findField('EvnUslugaDispOrp_setDate').focus(false)},
					icon: Ext.Msg.WARNING,
					msg: lang['data_okonchaniya_vyipolneniya_uslugi_ne_mojet_byit_menshe_datyi_nachala_vyipolneniya_uslugi'],
					title: lang['oshibka']
				});
				return false;
			}
		}

		if ( ( set_date.getMonthsBetween(did_date) > 3 ) || ( set_date.getMonthsBetween(did_date) == 3 && (set_date.getDate() != did_date.getDate()) ) )
		{
			Ext.MessageBox.show({
                buttons: Ext.Msg.OK,
                fn: function() {
                	current_window.findById('eudo13ewEvnUslugaDispOrp_setDate').focus(false);
                },
                icon: Ext.Msg.WARNING,
                msg: lang['data_polucheniya_rezultata_laboratornogo_issledovaniya_ne_bolee_3-h_mesyatsev_s_datyi_issledovaniya'],
                title: lang['oshibka']
            });
            return false;
		}
		
		var pl_set_date = current_window.set_date;

		var dop_disp_info_consent_id = null;
		var dop_disp_info_consent_is_earlier = null;

		var usluga_complex_code = base_form.findField('UslugaComplex_id').getFieldValue('UslugaComplex_Code');
		var item = null;
		for(var key in this.dopDispInfoConsentData) {
			if (typeof this.dopDispInfoConsentData[key] == 'object' && this.dopDispInfoConsentData[key].UslugaComplex_Code == usluga_complex_code) {
				item = this.dopDispInfoConsentData[key];
			}
		}
		dop_disp_info_consent_id = item.DopDispInfoConsent_id;
		dop_disp_info_consent_is_earlier = item.DopDispInfoConsent_IsEarlier;

		if (!dop_disp_info_consent_is_earlier && set_date < this.EvnPLDispOrp_setDate) {
			Ext.MessageBox.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					current_window.findById('eudo13ewEvnUslugaDispOrp_setDate').focus(false);
				},
				icon: Ext.Msg.WARNING,
				msg: lang['data_vyipolneniya_osmotra_issledovaniya_ne_doljna_byit_ranshe_datyi_nachala_dispanserizatsii'],
				title: lang['oshibka']
			});
			return false;
		}

		if (record_status == 1)
		{
			record_status = 2;
		}

		index = current_window.findById('eudo13ewLpuSectionCombo').getStore().findBy(function(rec) { return rec.get('LpuSection_id') == lpu_section_id; });
		
		if (index >= 0)
		{
			lpu_section_name = current_window.findById('eudo13ewLpuSectionCombo').getStore().getAt(index).data.LpuSection_Name;
		}

		var med_personal_fio = '';
		var med_personal_id = null;
		
		var record = current_window.findById('eudo13ewMedPersonalCombo').getStore().getById(med_staff_fact_id);
		
		if ( record ) {
			med_personal_fio = record.get('MedPersonal_Fio');
			med_personal_id = record.get('MedPersonal_id');
		}

		index = current_window.findById('eudo13ewUslugaComplexCombo').getStore().findBy(function(rec) { return rec.get('UslugaComplex_id') == uslugacomplex_id; });
		if (index >= 0)
		{
			uslugacomplex_code = current_window.findById('eudo13ewUslugaComplexCombo').getStore().getAt(index).data.UslugaComplex_Code;
			uslugacomplex_name = current_window.findById('eudo13ewUslugaComplexCombo').getStore().getAt(index).data.UslugaComplex_Name;
		}
		
		if (current_window.action != 'add')
		{
			add_flag = false;
		}
		var data = [{
			'EvnUslugaDispOrp_id': current_window.findById('eudo13ewEvnUslugaDispOrp_id').getValue(),
			'EvnUslugaDispOrp_setDate': base_form.findField('EvnUslugaDispOrp_setDate').getValue(),
			'EvnUslugaDispOrp_setTime': base_form.findField('EvnUslugaDispOrp_setTime').getValue(),
			'EvnUslugaDispOrp_disDate': base_form.findField('EvnUslugaDispOrp_disDate').getValue(),
			'EvnUslugaDispOrp_disTime': base_form.findField('EvnUslugaDispOrp_disTime').getValue(),
			'EvnUslugaDispOrp_didDate': base_form.findField('EvnUslugaDispOrp_didDate').getValue(),
			'LpuSection_id': lpu_section_id,
			'Lpu_uid': base_form.findField('Lpu_uid').getValue(),
			'MedSpecOms_id': base_form.findField('MedSpecOms_id').getValue(),
			'LpuSectionProfile_id': base_form.findField('LpuSectionProfile_id').getValue(),
			'ExaminationPlace_id': examination_place_id,
			'LpuSection_Name': lpu_section_name,
			'MedStaffFact_id': med_staff_fact_id,
			'MedPersonal_id': med_personal_id,
			'MedPersonal_Fio': med_personal_fio,
			'UslugaComplex_id': uslugacomplex_id,
			'UslugaComplex_Code': uslugacomplex_code,
			'UslugaComplex_Name': uslugacomplex_name,
			'EvnUslugaDispOrp_Result': base_form.findField('EvnUslugaDispOrp_Result').getValue(),
			'Record_Status': record_status
		}];
		current_window.callback(data, add_flag);
		current_window.hide();
    },
	draggable: true,
	id: 'EvnUslugaDispOrp13EditWindow',
    initComponent: function() {
		var win = this;
		
        Ext.apply(this, {
			buttons: [{
				handler: function() {
					win.doSave();
				},
				iconCls: 'save16',
				id: 'eudo13ewSaveButton',
				tabIndex: TABINDEX_EUDO13EF + 13,
				text: BTN_FRMSAVE
			}, {
				text: '-'
			},
				HelpButton(win, -1),
			{
				handler: function()
				{
					win.hide();
				},
				iconCls: 'cancel16',
				id: 'eudo13ewCancelButton',
				onTabAction: function() {
					Ext.getCmp('eudo13ewEvnUslugaDispOrp_setDate').focus(true, 200);
				},
				onShiftTabAction: function() {
					Ext.getCmp('eudo13ewSaveButton').focus(true, 200);
				},
				tabIndex: TABINDEX_EUDO13EF + 14,
				text: BTN_FRMCANCEL
			}],
            items: [
				new	sw.Promed.PersonInformationPanelShort({
					id: 'eudo13ewPersonInformationFrame',
					region: 'north'
				}),
				new Ext.form.FormPanel({
					autoHeight: true,
					bodyBorder: false,
					bodyStyle: 'padding: 5px 5px 0',
					border: false,
					frame: false,
					id: 'EvnUslugaDispOrp13EditForm',
					labelAlign: 'right',
					labelWidth: 180,
					items: [{
						id: 'eudo13ewEvnUslugaDispOrp_id',
						name: 'EvnUslugaDispOrp_id',
						value: 0,
						xtype: 'hidden'
					}, {
						id: 'eudo13ewRecord_Status',
						name: 'Record_Status',
						value: 0,
						xtype: 'hidden'
					}, {
						name: 'MedPersonal_id',
						xtype: 'hidden'
					}, {
						border: false,
						layout: 'column',
						items: [{
							border: false,
							layout: 'form',
							labelWidth: 180,
							items: [{
								allowBlank: false,
								enableKeyEvents: true,
								fieldLabel: lang['data_nachala_vyipolneniya'],
								format: 'd.m.Y',
								id: 'eudo13ewEvnUslugaDispOrp_setDate',
								listeners: {
									'keydown':  function(inp, e) {
										if ( e.shiftKey && e.getKey() == Ext.EventObject.TAB )
										{
											e.stopEvent();
											Ext.getCmp('eudo13ewCancelButton').focus(true, 200);
										}
									},
									'change': function(field, newValue, oldValue) {
										if ( blockedDateAfterPersonDeath('personpanelid', 'eudo13ewPersonInformationFrame', field, newValue, oldValue) ) {
											return false;
										}

										var base_form = this.findById('EvnUslugaDispOrp13EditForm').getForm();

										if ( !Ext.isEmpty(newValue) && Ext.isEmpty(base_form.findField('EvnUslugaDispOrp_didDate').getValue()) ) {
											base_form.findField('EvnUslugaDispOrp_didDate').setValue(newValue);
										}

										this.filterLpuCombo();
										this.setLpuSectionAndMedStaffFactFilter();
										this.filterProfileAndMedSpec();
										this.setDisDT();
									}.createDelegate(this)
								},
								name: 'EvnUslugaDispOrp_setDate',
								maxValue: Date.parseDate(getGlobalOptions().date, 'd.m.Y'),
								plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
								tabIndex: TABINDEX_EUDO13EF,
								width: 100,
								xtype: 'swdatefield'
							}]
						}, {
							border: false,
							layout: 'form',
							labelWidth: 50,
							items: [{
								fieldLabel: lang['vremya'],
								listeners: {
									'change': function() {
										this.setDisDT();
									}.createDelegate(this),
									'keydown': function (inp, e) {
										if ( e.getKey() == Ext.EventObject.F4 ) {
											e.stopEvent();
											inp.onTriggerClick();
										}
									}
								},
								id: 'eudo13ewEvnUslugaDispOrp_setTime',
								name: 'EvnUslugaDispOrp_setTime',
								onTriggerClick: function() {
									var base_form = this.findById('EvnUslugaDispOrp13EditForm').getForm();

									var time_field = base_form.findField('EvnUslugaDispOrp_setTime');

									if ( time_field.disabled ) {
										return false;
									}

									setCurrentDateTime({
										callback: function() {
											if ( Ext.isEmpty(base_form.findField('EvnUslugaDispOrp_didDate').getValue()) ) {
												base_form.findField('EvnUslugaDispOrp_didDate').setValue(base_form.findField('EvnUslugaDispOrp_setDate').getValue());
												base_form.findField('EvnUslugaDispOrp_didDate').fireEvent('change', base_form.findField('EvnUslugaDispOrp_didDate'), base_form.findField('EvnUslugaDispOrp_didDate').getValue());
											}

											this.setDisDT();
										}.createDelegate(this),
										dateField: base_form.findField('EvnUslugaDispOrp_setDate'),
										loadMask: true,
										setDate: true,
										setDateMaxValue: true,
										setDateMinValue: false,
										setTime: true,
										timeField: time_field,
										windowId: this.id
									});
								}.createDelegate(this),
								plugins: [ new Ext.ux.InputTextMask('99:99', true) ],
								tabIndex: TABINDEX_EUDO13EF + 1,
								validateOnBlur: false,
								width: 60,
								xtype: 'swtimefield'
							}]
						}, {
							layout: 'form',
							style: 'padding-left: 45px',
							border: false,
							items: [{
								xtype: 'button',
								id: 'eudo13ew_ToggleVisibleDisDTBtn',
								text: lang['utochnit_period_vyipolneniya'],
								handler: function() {
									this.toggleVisibleDisDTPanel();
								}.createDelegate(this)
							}]
						}]
					}, {
						border: false,
						layout: 'column',
						id: 'eudo13ew_EvnUslugaDisDTPanel',
						items: [{
							border: false,
							layout: 'form',
							labelWidth: 180,
							items: [{
								fieldLabel: lang['data_okonchaniya_vyipolneniya'],
								format: 'd.m.Y',
								id: 'eudo13ewEvnUslugaDispOrp_disDate',
								name: 'EvnUslugaDispOrp_disDate',
								maxValue: Date.parseDate(getGlobalOptions().date, 'd.m.Y'),
								plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
								tabIndex: TABINDEX_EUDO13EF + 2,
								width: 100,
								xtype: 'swdatefield'
							}]
						}, {
							border: false,
							layout: 'form',
							labelWidth: 50,
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
								id: 'eudo13ewEvnUslugaDispOrp_disTime',
								name: 'EvnUslugaDispOrp_disTime',
								onTriggerClick: function() {
									var base_form = this.findById('EvnUslugaDispOrp13EditForm').getForm();

									var time_field = base_form.findField('EvnUslugaDispOrp_disTime');

									if ( time_field.disabled ) {
										return false;
									}

									setCurrentDateTime({
										dateField: base_form.findField('EvnUslugaDispOrp_disDate'),
										loadMask: true,
										setDate: true,
										setDateMaxValue: true,
										setDateMinValue: false,
										setTime: true,
										timeField: time_field,
										windowId: this.id
									});
								}.createDelegate(this),
								plugins: [ new Ext.ux.InputTextMask('99:99', true) ],
								tabIndex: TABINDEX_EUDO13EF + 3,
								validateOnBlur: false,
								width: 60,
								xtype: 'swtimefield'
							}]
						}, {
							layout: 'form',
							border: false,
							items: [{
								xtype: 'button',
								id: 'eudo13ew_DTCopyBtn',
								text: '=',
								handler: function() {
									var base_form = this.findById('EvnUslugaDispOrp13EditForm').getForm();

									base_form.findField('EvnUslugaDispOrp_disDate').setValue(base_form.findField('EvnUslugaDispOrp_setDate').getValue());
									base_form.findField('EvnUslugaDispOrp_disTime').setValue(base_form.findField('EvnUslugaDispOrp_setTime').getValue());
								}.createDelegate(this)
							}]
						}]
					}, {
						allowBlank: false,
						fieldLabel: lang['data_rezultata'],
						format: 'd.m.Y',
						id: 'eudo13ewEvnUslugaDispOrp_didDate',
						name: 'EvnUslugaDispOrp_didDate',
						maxValue: Date.parseDate(getGlobalOptions().date, 'd.m.Y'),
						plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
						tabIndex: TABINDEX_EUDO13EF + 4,
						width: 100,
						xtype: 'swdatefield'
					}, {
						allowBlank: false,
						enableKeyEvents: true,
						id: 'eudo13ewExaminationPlaceCombo',
						listeners: {
							'change': function(field, newValue, oldValue) {
								var base_form = win.findById('EvnUslugaDispOrp13EditForm').getForm();
								
								if ( getRegionNick().inlist([ 'perm' ]) ) {
									var index = base_form.findField('LpuSectionProfile_id').getStore().findBy(function(rec) {
										return (rec.get('LpuSectionProfile_id') == base_form.findField('LpuSectionProfile_id').getValue());
									});
									
									if (base_form.findField('LpuSectionProfile_id').getStore().getCount() == 1) {
										ucid = base_form.findField('LpuSectionProfile_id').getStore().getAt(0).get('LpuSectionProfile_id');
										base_form.findField('LpuSectionProfile_id').setValue(ucid);
									} else if (base_form.findField('LpuSectionProfile_id').getStore().getCount() > 1) {
										if ( index >= 0 ) {
											ucid = base_form.findField('LpuSectionProfile_id').getStore().getAt(index).get('LpuSectionProfile_id');
										} else {
											ucid = base_form.findField('LpuSectionProfile_id').getStore().getAt(0).get('LpuSectionProfile_id');
										}
										base_form.findField('LpuSectionProfile_id').setValue(ucid);
									}
									
									base_form.findField('LpuSectionProfile_id').fireEvent('change', base_form.findField('LpuSectionProfile_id'), base_form.findField('LpuSectionProfile_id').getValue());
									
									var index = base_form.findField('MedSpecOms_id').getStore().findBy(function(rec) {
										return (rec.get('MedSpecOms_id') == base_form.findField('MedSpecOms_id').getValue());
									});
									
									if (base_form.findField('MedSpecOms_id').getStore().getCount() == 1) {
										ucid = base_form.findField('MedSpecOms_id').getStore().getAt(0).get('MedSpecOms_id');
										base_form.findField('MedSpecOms_id').setValue(ucid);
									} else if (base_form.findField('MedSpecOms_id').getStore().getCount() > 1) {
										if ( index >= 0 ) {
											ucid = base_form.findField('MedSpecOms_id').getStore().getAt(index).get('MedSpecOms_id');
										} else {
											ucid = base_form.findField('MedSpecOms_id').getStore().getAt(0).get('MedSpecOms_id');
										}
										base_form.findField('MedSpecOms_id').setValue(ucid);
									}
									
									base_form.findField('MedSpecOms_id').fireEvent('change', base_form.findField('MedSpecOms_id'), base_form.findField('MedSpecOms_id').getValue());
								}
								
								win.setLpuSectionAndMedStaffFactFilter();
							}.createDelegate(this)
						},
						name: 'ExaminationPlace_id',
						tabIndex: TABINDEX_EUDO13EF + 5,
						validateOnBlur: false,
						width: 350,
						xtype: 'swexaminationplacecombo'
					}, {
						id: 'eudo13ewLpuCombo',
						comboSubject: 'Lpu',
						fieldLabel: lang['mo'],
						xtype: 'swcommonsprcombo',
						editable: true,
						forceSelection: true,
						displayField: 'Lpu_Nick',
						codeField: 'Lpu_Code',
						orderBy: 'Nick',
						tpl: new Ext.XTemplate(
							'<tpl for="."><div class="x-combo-list-item">',
							'{Lpu_Nick}',
							'</div></tpl>'
						),
						moreFields: [
							{name: 'Lpu_Nick', mapping: 'Lpu_Nick'},
							{name: 'Lpu_EndDate', mapping: 'Lpu_EndDate'}
						],
						tabIndex: TABINDEX_EUDO13EF + 6,
						width: 450,
						hiddenName: 'Lpu_uid',
						onLoadStore: function() {
							win.filterLpuCombo();
						},
						listeners: {
							'change': function(field, newValue, oldValue) {
								win.setLpuSectionAndMedStaffFactFilter();
							}
						}
					}, {
						fieldLabel: lang['profil'],
						xtype: 'swlpusectionprofileremotecombo',
						tabIndex: TABINDEX_EUDO13EF + 7,
						width: 450,
						hiddenName: 'LpuSectionProfile_id',
						listeners: {
							'change': function(field, newValue, oldValue) {
								win.setLpuSectionAndMedStaffFactFilter();
							}
						}
					}, {
						fieldLabel: lang['spetsialnost'],
						xtype: 'swmedspecomsremotecombo',
						tabIndex: TABINDEX_EUDO13EF + 8,
						width: 450,
						hiddenName: 'MedSpecOms_id',
						listeners: {
							'change': function(field, newValue, oldValue) {
								win.setLpuSectionAndMedStaffFactFilter();
							}
						}
					}, {
						hiddenName: 'LpuSection_id',
						id: 'eudo13ewLpuSectionCombo',
						lastQuery: '',
						listWidth: 650,
						linkedElements: [
							'eudo13ewMedPersonalCombo'
						],
						listeners: {
							'select': function(combo, record, index) {
								combo.setValue(record.get('LpuSection_id'));
								combo.fireEvent('change', combo, combo.getValue());
							},
							'change': function (field, newValue, oldValue) {
								if (getRegionNick() == 'ufa') {
									// услуга зависит от выбранного отделения
									win.loadUslugaComplexCombo();
								}
							}
						},
						tabIndex: TABINDEX_EUDO13EF + 9,
						width: 450,
						xtype: 'swlpusectionglobalcombo',
						parentElementId: 'eudo13ewLpuCombo',
						allowBlank: true//!(getRegionNick() == 'buryatiya')
					}, {
						hiddenName: 'MedStaffFact_id',
						id: 'eudo13ewMedPersonalCombo',
						lastQuery: '',
						listWidth: 650,
						parentElementId: 'eudo13ewLpuSectionCombo',
						listeners: {
							'change': function(field, newValue, oldValue) {
								if (getRegionNick().inlist(['kareliya', 'penza']) && !Ext.isEmpty(newValue)) {
									var index = field.getStore().findBy(function (rec) {
										return (rec.get('MedStaffFact_id') == newValue);
									});
									if (index >= 0) {
										var
											MedSpecOms_id = field.getStore().getAt(index).get('MedSpecOms_id'),
											MedPersonal_Snils = field.getStore().getAt(index).get('Person_Snils');
										if (Ext.isEmpty(MedSpecOms_id)) {
											sw.swMsg.alert(lang['soobschenie'], lang['u_vracha_ne_ukazana_spetsialnost']);
											return false;
										} else if (Ext.isEmpty(MedPersonal_Snils)) {
											sw.swMsg.alert(lang['soobschenie'], lang['u_vracha_ne_ukazan_snils']);
											return false;
										}
									}
								}
								if (getRegionNick() == 'ufa') {
									// услуга зависит от выбранного отделения
									win.loadUslugaComplexCombo();
								}
							}
						},
						tabIndex: TABINDEX_EUDO13EF + 10,
						width: 450,
						xtype: 'swmedstafffactglobalcombo',
						allowBlank: true//!(getRegionNick() == 'buryatiya')
					}, {
						allowBlank: false,
						id: 'eudo13ewUslugaComplexCombo',
						listWidth: 500,
						tabIndex: TABINDEX_EUDO13EF + 11,
						width: 450,
						nonDispOnly: false,
						xtype: 'swuslugacomplexnewcombo'
					}, {
						allowBlank: false,
						fieldLabel: 'Результат',
						tabIndex: TABINDEX_EUDO13EF + 12,
						width: 450,
						name: 'EvnUslugaDispOrp_Result',
						xtype: 'textfield'
					}],
					layout: 'form',
					reader: new Ext.data.JsonReader({
						success: function() { }
					}, [
						{ name: 'EvnUslugaDispOrp_id' }
					]),
					region: 'center'
				})
			]
        });
		
    	sw.Promed.swEvnUslugaDispOrp13EditWindow.superclass.initComponent.apply(this, arguments);
		
		this.findById('eudo13ewUslugaComplexCombo').addListener('change', function(combo, newValue, oldValue) {
			this.filterProfileAndMedSpec();
		}.createDelegate(this));
    },
	filterProfileAndMedSpec: function() {
		var win = this;		
		var base_form = this.findById('EvnUslugaDispOrp13EditForm').getForm();
		
		if (getRegionNick() == 'ekb') {
			win.MedSpecOms_id = null;
			
			for(var key in this.dopDispInfoConsentData) {
				if (typeof this.dopDispInfoConsentData[key] == 'object' && !Ext.isEmpty(this.dopDispInfoConsentData[key].UslugaComplex_Code) && this.dopDispInfoConsentData[key].UslugaComplex_Code == base_form.findField('UslugaComplex_id').getFieldValue('UslugaComplex_Code')) {
					win.MedSpecOms_id = this.dopDispInfoConsentData[key].MedSpecOms_id;
				}
			}
		}
		
		var curDate = getGlobalOptions().date;
		if ( !Ext.isEmpty(base_form.findField('EvnUslugaDispOrp_setDate').getValue()) ) {
			curDate = Ext.util.Format.date(base_form.findField('EvnUslugaDispOrp_setDate').getValue(), 'd.m.Y');
		}

		var
			LpuSectionProfile_id = base_form.findField('LpuSectionProfile_id').getValue(),
			MedSpecOms_id = base_form.findField('MedSpecOms_id').getValue();

		base_form.findField('LpuSectionProfile_id').getStore().removeAll();
		base_form.findField('MedSpecOms_id').getStore().removeAll();
		if (!Ext.isEmpty(base_form.findField('UslugaComplex_id').getValue())) {
			// загружаем списки Профиль и Специальность в зависимости от Услуги
			base_form.findField('LpuSectionProfile_id').getStore().load({
				params: {
					UslugaComplex_id: base_form.findField('UslugaComplex_id').getValue(),
					onDate: curDate,
					DispClass_id: win.DispClass_id
				},
				callback: function() {
					if ( !Ext.isEmpty(LpuSectionProfile_id) ) {
						base_form.findField('LpuSectionProfile_id').setValue(LpuSectionProfile_id);
					}
					base_form.findField('ExaminationPlace_id').fireEvent('change', base_form.findField('ExaminationPlace_id'), base_form.findField('ExaminationPlace_id').getValue());
				}
			});
			base_form.findField('MedSpecOms_id').getStore().load({
				params: {
					UslugaComplex_id: base_form.findField('UslugaComplex_id').getValue(),
					onDate: curDate,
					DispClass_id: win.DispClass_id
				},
				callback: function() {
					if ( !Ext.isEmpty(MedSpecOms_id) ) {
						base_form.findField('MedSpecOms_id').setValue(MedSpecOms_id);
					}
					base_form.findField('ExaminationPlace_id').fireEvent('change', base_form.findField('ExaminationPlace_id'), base_form.findField('ExaminationPlace_id').getValue());
				}
			});
		}
	},
    keys: [{
    	alt: true,
        fn: function(inp, e) {
            e.stopEvent();

            if (e.browserEvent.stopPropagation)
                e.browserEvent.stopPropagation();
            else
                e.browserEvent.cancelBubble = true;

            if (e.browserEvent.preventDefault)
                e.browserEvent.preventDefault();
            else
                e.browserEvent.returnValue = false;

            e.browserEvent.returnValue = false;
            e.returnValue = false;

            if (Ext.isIE)
            {
            	e.browserEvent.keyCode = 0;
            	e.browserEvent.which = 0;
            }

        	var current_window = Ext.getCmp('EvnUslugaDispOrp13EditWindow');

            if (e.getKey() == Ext.EventObject.J)
            {
            	current_window.hide();
            }
			else if (e.getKey() == Ext.EventObject.C)
			{
	        	if ('view' != current_window.action)
	        	{
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
    maximizable: true,
    minHeight: 300,
    minWidth: 700,
    modal: true,
    onHide: Ext.emptyFn,
	ownerWindow: null,
    plain: true,
    resizable: true,
	filterLpuCombo: function() {
		var base_form = this.findById('EvnUslugaDispOrp13EditForm').getForm();
		// фильтр на МО (отображать только открытые действующие)
		var curDate = Date.parseDate(getGlobalOptions().date, 'd.m.Y');
		if ( !Ext.isEmpty(base_form.findField('EvnUslugaDispOrp_setDate').getValue()) ) {
			curDate = base_form.findField('EvnUslugaDispOrp_setDate').getValue();
		}
		base_form.findField('Lpu_uid').lastQuery = '';
		base_form.findField('Lpu_uid').getStore().clearFilter();
		base_form.findField('Lpu_uid').setBaseFilter(function(rec, id) {
			if (!Ext.isEmpty(rec.get('Lpu_EndDate'))) {
				var lpuEndDate = Date.parseDate(rec.get('Lpu_EndDate'), 'd.m.Y');
				if (lpuEndDate < curDate) {
					return false;
				}
			}
			if (!Ext.isEmpty(getGlobalOptions().lpu_id) && rec.get('Lpu_id') == getGlobalOptions().lpu_id) {
				return false;
			}
			return true;
		});
	},
	uslugaComplexAllowed: [],
	setLpuSectionAndMedStaffFactFilter: function() {
		var win = this;
		var base_form = this.findById('EvnUslugaDispOrp13EditForm').getForm();

		// Учитываем дату и место выполнения
		var EvnUslugaDispOrp_setDate = base_form.findField('EvnUslugaDispOrp_setDate').getValue();
		var ExaminationPlace_id = base_form.findField('ExaminationPlace_id').getValue();
		var MedStaffFact_id = base_form.findField('MedStaffFact_id').getValue();

		base_form.findField('LpuSection_id').enableLinkedElements();
		base_form.findField('MedStaffFact_id').enableParentElement();

		if ( !Ext.isEmpty(ExaminationPlace_id) && ExaminationPlace_id == 3 ) {
			if(getRegionNick()== 'buryatiya' && base_form.findField('Lpu_uid').getValue()==getGlobalOptions().lpu_id){
				base_form.findField('LpuSection_id').clearValue();
				base_form.findField('MedStaffFact_id').clearValue();
				base_form.findField('Lpu_uid').clearValue();
			}
			// показать поля МО, Профиль, Специальность
			base_form.findField('Lpu_uid').showContainer();

			if ( getRegionNick().inlist([ 'buryatiya', 'ekb', 'kareliya', 'penza', 'pskov', 'adygeya' ]) ) {
				base_form.findField('LpuSectionProfile_id').hideContainer();
				base_form.findField('MedSpecOms_id').hideContainer();
			}
			else {
				base_form.findField('LpuSectionProfile_id').showContainer();
				base_form.findField('MedSpecOms_id').showContainer();
			}

			base_form.findField('Lpu_uid').setAllowBlank(getRegionNick().inlist([ 'pskov', 'ufa', 'ekb' ]));
			base_form.findField('LpuSectionProfile_id').setAllowBlank(getRegionNick().inlist([ 'pskov', 'ufa', 'ekb', 'kareliya', 'penza', 'buryatiya', 'adygeya' ]));
			base_form.findField('MedSpecOms_id').setAllowBlank(getRegionNick().inlist([ 'pskov', 'ufa', 'ekb', 'kareliya', 'penza', 'buryatiya', 'adygeya' ]));
			base_form.findField('LpuSection_id').setAllowBlank(getRegionNick().inlist([ 'krym' ]));
			base_form.findField('MedStaffFact_id').setAllowBlank(getRegionNick().inlist([ 'krym' ]));

			if ( getRegionNick().inlist([ 'krym', 'perm' ]) ) {
				base_form.findField('LpuSection_id').disableLinkedElements();
				base_form.findField('MedStaffFact_id').disableParentElement();
			}

			if (
				(!getRegionNick().inlist([ 'buryatiya', 'ekb', 'kareliya', 'penza', 'pskov', 'adygeya' ]) && Ext.isEmpty(base_form.findField('LpuSectionProfile_id').getValue()))
				|| Ext.isEmpty(base_form.findField('Lpu_uid').getValue())
			) {
				base_form.findField('LpuSection_id').getStore().removeAll();
				base_form.findField('LpuSection_id').clearValue();
				win.lastLpuSectionProfile_id = base_form.findField('LpuSectionProfile_id').getValue();
				win.lastLpu_uid1 = base_form.findField('Lpu_uid').getValue();
			}
			
			if (
				(!getRegionNick().inlist([ 'buryatiya', 'ekb', 'kareliya', 'penza', 'pskov', 'adygeya' ]) && Ext.isEmpty(base_form.findField('MedSpecOms_id').getValue()))
				|| Ext.isEmpty(base_form.findField('Lpu_uid').getValue())
			) {
				base_form.findField('MedStaffFact_id').getStore().removeAll();
				base_form.findField('MedStaffFact_id').clearValue();
				win.lastMedSpecOms_id = base_form.findField('MedSpecOms_id').getValue();
				win.lastLpu_uid2 = base_form.findField('Lpu_uid').getValue();
			}

			var setDate = (!Ext.isEmpty(EvnUslugaDispOrp_setDate) ? Ext.util.Format.date(EvnUslugaDispOrp_setDate, 'd.m.Y') : null);
			
			if (
				(!Ext.isEmpty(base_form.findField('LpuSectionProfile_id').getValue()) || getRegionNick().inlist([ 'buryatiya', 'ekb', 'kareliya', 'penza', 'pskov', 'adygeya' ]))
				&& !Ext.isEmpty(base_form.findField('Lpu_uid').getValue())
				&& (
					base_form.findField('Lpu_uid').getValue() != win.lastLpu_uid1
					|| setDate != win.lastSetDate1
					|| (!getRegionNick().inlist([ 'buryatiya', 'ekb', 'kareliya', 'penza', 'pskov', 'adygeya' ]) && base_form.findField('LpuSectionProfile_id').getValue() != win.lastLpuSectionProfile_id)
				)
				||
				(
					getRegionNick().inlist([ 'buryatiya' ])
					&& Ext.isEmpty(base_form.findField('Lpu_uid').getValue())
				)
			) {
				win.lastLpuSectionProfile_id = base_form.findField('LpuSectionProfile_id').getValue();
				win.lastLpu_uid1 = base_form.findField('Lpu_uid').getValue();
				win.lastSetDate1 = setDate;
				
				base_form.findField('LpuSection_id').getStore().load({
					callback: function() {
						var store = base_form.findField('LpuSection_id').getStore();
						var ucid = null;
						var index = store.findBy(function (rec) {
							return (rec.get('LpuSection_id') == base_form.findField('LpuSection_id').getValue());
						});

						if (
							!(getRegionNick().inlist(['buryatiya']) && Ext.isEmpty(base_form.findField('Lpu_uid').getValue()))
						) {
							if (index >= 0) {
								ucid = store.getAt(index).get('LpuSection_id');
							} else if (store.getCount() && win.loadFirstMedPersonal) {
								ucid = store.getAt(0).get('LpuSection_id');
							}

							if (ucid) {
								base_form.findField('LpuSection_id').setValue(ucid);
							} else {
								base_form.findField('LpuSection_id').clearValue();
							}
						}
					}.createDelegate(this),
					params: {
						date: setDate,
						LpuSectionProfile_id: base_form.findField('LpuSectionProfile_id').getValue(),
						Lpu_id: base_form.findField('Lpu_uid').getValue(),
						mode: (getRegionNick().inlist([ 'krym', 'perm' ]))?'combo':'dispcontractcombo'
					}
				});
			}
			
			if (
				(!Ext.isEmpty(base_form.findField('MedSpecOms_id').getValue()) || getRegionNick().inlist([ 'buryatiya', 'ekb', 'kareliya', 'penza', 'pskov', 'adygeya' ]))
				&& !Ext.isEmpty(base_form.findField('Lpu_uid').getValue())
				&& (
					base_form.findField('Lpu_uid').getValue() != win.lastLpu_uid2
					|| setDate != win.lastSetDate2
					|| (!getRegionNick().inlist([ 'buryatiya', 'ekb', 'kareliya', 'penza', 'pskov', 'adygeya' ]) && base_form.findField('MedSpecOms_id').getValue() != win.lastMedSpecOms_id)
				)
				||
				(
					getRegionNick().inlist([ 'buryatiya' ])
					&& Ext.isEmpty(base_form.findField('Lpu_uid').getValue())
				)
			) {
				win.lastMedSpecOms_id = base_form.findField('MedSpecOms_id').getValue();
				win.lastLpu_uid2 = base_form.findField('Lpu_uid').getValue();
				win.lastSetDate2 = setDate;

				base_form.findField('MedStaffFact_id').getStore().load({
					callback: function() {
						var store = base_form.findField('MedStaffFact_id').getStore();
						var ucid = null;
						var index = store.findBy(function(rec) {
							return (rec.get('MedStaffFact_id') == MedStaffFact_id);
						});
						if ( index < 0 ) {
							index = store.findBy(function(rec) {
								return (rec.get('MedPersonal_id') == base_form.findField('MedPersonal_id').getValue());
							});
						}

						if (
							!(getRegionNick().inlist(['buryatiya']) && Ext.isEmpty(base_form.findField('Lpu_uid').getValue()))
						) {
							if (index >= 0) {
								ucid = store.getAt(index).get('MedStaffFact_id');
							} else if (store.getCount() && win.loadFirstMedPersonal) {
								ucid = store.getAt(0).get('MedStaffFact_id');
							}

							if (ucid) {
								base_form.findField('MedStaffFact_id').setValue(ucid);
								base_form.findField('LpuSection_id').setValue(base_form.findField('MedStaffFact_id').getFieldValue('LpuSection_id'));
								base_form.findField('LpuSection_id').fireEvent('change', base_form.findField('LpuSection_id'), base_form.findField('LpuSection_id').getValue());
							} else {
								base_form.findField('MedStaffFact_id').clearValue();
							}
						}
					}.createDelegate(this),
					params: {
						onDate: setDate,
						mode: (getRegionNick().inlist([ 'krym', 'perm' ]))?'combo':'dispcontractcombo',
						MedSpecOms_id: base_form.findField('MedSpecOms_id').getValue(),
						Lpu_id: base_form.findField('Lpu_uid').getValue()
					}
				});
			}
		} else {
			// скрыть поля МО, Профиль, Специальность
			base_form.findField('Lpu_uid').clearValue();
			base_form.findField('Lpu_uid').setAllowBlank(true);
			base_form.findField('Lpu_uid').hideContainer();
			base_form.findField('LpuSectionProfile_id').clearValue();
			base_form.findField('LpuSectionProfile_id').setAllowBlank(true);
			base_form.findField('LpuSectionProfile_id').hideContainer();
			base_form.findField('MedSpecOms_id').clearValue();
			base_form.findField('MedSpecOms_id').setAllowBlank(true);
			base_form.findField('MedSpecOms_id').hideContainer();
			/*
			if (getRegionNick().inlist(['buryatiya'])) {
				base_form.findField('LpuSection_id').disableLinkedElements();
				base_form.findField('MedStaffFact_id').disableParentElement();
			}*/

			base_form.findField('LpuSection_id').setAllowBlank(false);
			base_form.findField('MedStaffFact_id').setAllowBlank(false);
			
			var index;
			var params = new Object();

			if ( !Ext.isEmpty(EvnUslugaDispOrp_setDate) ) {
				params.onDate = Ext.util.Format.date(EvnUslugaDispOrp_setDate, 'd.m.Y');
			}
			
			if ( !getRegionNick().inlist(['ekb']) ) {
				if ( !Ext.isEmpty(ExaminationPlace_id) ) {
					if ( ExaminationPlace_id == 3 ) {
						params.isAliens = true;
					}

					if ( ExaminationPlace_id == 2 ) {
						params.isStac = true;
					}
					else {
						params.isNotStac = true;
					}
				}
			}
			
			if ( getRegionNick().inlist(['ekb']) ) {
				if (!Ext.isEmpty(this.MedSpecOms_id)) {
					params.MedSpecOms_id = this.MedSpecOms_id;
				}
			}

			// Сохраняем текущие значения
			var LpuSection_id = base_form.findField('LpuSection_id').getValue();
			var MedStaffFact_id = base_form.findField('MedStaffFact_id').getValue();

			base_form.findField('LpuSection_id').clearValue();
			base_form.findField('MedStaffFact_id').clearValue();

			setLpuSectionGlobalStoreFilter(params);
			setMedStaffFactGlobalStoreFilter(params);

			base_form.findField('LpuSection_id').getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));
			base_form.findField('MedStaffFact_id').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));

			index = base_form.findField('LpuSection_id').getStore().findBy(function(rec) {
				return (rec.get('LpuSection_id') == LpuSection_id);
			});

			if ( index >= 0 ) {
				base_form.findField('LpuSection_id').setValue(LpuSection_id);
			}

			index = base_form.findField('MedStaffFact_id').getStore().findBy(function(rec) {
				return (rec.get('MedStaffFact_id') == MedStaffFact_id);
			});

			if ( index >= 0 ) {
				base_form.findField('MedStaffFact_id').setValue(MedStaffFact_id);
			}

			if(base_form.findField('LpuSection_id').isExpanded()){
				base_form.findField('LpuSection_id').collapse();
			}
		}

		this.syncSize();
		this.syncShadow();
	},
	dopDispInfoConsentData: [],
	loadUslugaComplexCombo: function() {
		var win = this;
		var base_form = this.findById('EvnUslugaDispOrp13EditForm').getForm();

		if (getRegionNick() == 'ufa') {
			// услуга зависит от выбранного отделения
			base_form.findField('UslugaComplex_id').getStore().baseParams.LpuSection_id = base_form.findField('LpuSection_id').getValue();
		}

		// повторно грузить одно и то же не нужно
		var newUslugaComplexParams = Ext.util.JSON.encode(base_form.findField('UslugaComplex_id').getStore().baseParams);
		if (newUslugaComplexParams != win.lastUslugaComplexParams) {
			var currentUslugaComplex_id = base_form.findField('UslugaComplex_id').getValue();
			win.getLoadMask(lang['zagruzka_spiska_vozmojnyih_uslug_pojaluysta_podojdite']).show();
			base_form.findField('UslugaComplex_id').clearValue();
			base_form.findField('UslugaComplex_id').getStore().removeAll();
			win.lastUslugaComplexParams = newUslugaComplexParams;
			base_form.findField('UslugaComplex_id').getStore().load({
				callback: function () {
					win.getLoadMask().hide();
					index = base_form.findField('UslugaComplex_id').getStore().findBy(function (rec) {
						return (rec.get('UslugaComplex_id') == currentUslugaComplex_id);
					});

					if (base_form.findField('UslugaComplex_id').getStore().getCount() == 1) {
						ucid = base_form.findField('UslugaComplex_id').getStore().getAt(0).get('UslugaComplex_id');
						base_form.findField('UslugaComplex_id').setValue(ucid);
					} else if (base_form.findField('UslugaComplex_id').getStore().getCount() > 1) {
						if (index >= 0) {
							ucid = base_form.findField('UslugaComplex_id').getStore().getAt(index).get('UslugaComplex_id');
						} else {
							ucid = base_form.findField('UslugaComplex_id').getStore().getAt(0).get('UslugaComplex_id');
						}
						base_form.findField('UslugaComplex_id').setValue(ucid);
					}

					base_form.findField('UslugaComplex_id').fireEvent('change', base_form.findField('UslugaComplex_id'), base_form.findField('UslugaComplex_id').getValue());
				}
			});
		}
	},
    show: function() {
		sw.Promed.swEvnUslugaDispOrp13EditWindow.superclass.show.apply(this, arguments);

		var current_window = this;

		current_window.restore();
		current_window.center();

        var form = current_window.findById('EvnUslugaDispOrp13EditForm');
		var base_form = form.getForm();
		form.getForm().reset();

       	current_window.callback = Ext.emptyFn;
       	current_window.onHide = Ext.emptyFn;
		current_window.ownerWindow = null;
		current_window.isVisibleDisDTPanel = false;
		current_window.loadFirstMedPersonal = true;

		current_window.toggleVisibleDisDTPanel('hide');
		
        if (!arguments[0] || !arguments[0].formParams || !arguments[0].ownerWindow || !arguments[0].DispClass_id)
        {
        	Ext.Msg.alert(lang['soobschenie'], lang['nevernyie_parametryi'], function() { current_window.hide(); } );
        	return false;
        }

		this.EvnPLDisp_id = null;
		if (arguments[0].EvnPLDisp_id)
		{
			this.EvnPLDisp_id = arguments[0].EvnPLDisp_id;
		}
		
		this.lastLpuSectionProfile_id = null;
		this.lastLpu_uid1 = null;
		this.lastSetDate1 = null;
		this.lastLpu_uid2 = null;
		this.lastSetDate2 = null;
		this.lastMedSpecOms_id = null;
		this.DispClass_id = arguments[0].DispClass_id;
		this.EvnPLDispOrp_setDate = arguments[0].EvnPLDispOrp_setDate || null;
		
		this.setLpuSectionAndMedStaffFactFilter();

		if ( getRegionNick() == 'ekb' ) {
			base_form.findField('UslugaComplex_id').getStore().baseParams.DispClass_id = this.DispClass_id;
		}

		if (getRegionNick() != 'kz' && getGlobalOptions().disp_control == 3) {
			base_form.findField('EvnUslugaDispOrp_Result').setAllowBlank(false);
		} else {
			base_form.findField('EvnUslugaDispOrp_Result').setAllowBlank(true);
		}

		base_form.findField('ExaminationPlace_id').lastQuery = '';
		base_form.findField('ExaminationPlace_id').getStore().filterBy(function(rec) {
			return rec.get('ExaminationPlace_Code').toString().inlist([ '1', '3' ]);
		});

        if (arguments[0].action)
        {
        	current_window.action = arguments[0].action;
        }
		
        if (arguments[0].uslugaComplexAllowed)
        {
        	current_window.uslugaComplexAllowed = arguments[0].uslugaComplexAllowed;
        }
		
		if (arguments[0].dopDispInfoConsentData)
        {
        	current_window.dopDispInfoConsentData = arguments[0].dopDispInfoConsentData;
        }
		
		this.MedSpecOms_id = null;
		
		if (arguments[0].set_date)
        {
        	current_window.set_date = arguments[0].set_date;
        }

        if (arguments[0].callback)
        {
            current_window.callback = arguments[0].callback;
        }

        if (arguments[0].onHide)
        {
        	current_window.onHide = arguments[0].onHide;
        }

        if (arguments[0].ownerWindow)
        {
        	current_window.ownerWindow = arguments[0].ownerWindow;
        }

		this.UslugaComplex_Date = null;
		if (arguments[0].UslugaComplex_Date)
		{
			this.UslugaComplex_Date = arguments[0].UslugaComplex_Date;
		}

		current_window.findById('eudo13ewPersonInformationFrame').load({
			Person_id: (arguments[0].Person_id ? arguments[0].Person_id : ''),
			Person_Birthday: (arguments[0].Person_Birthday ? arguments[0].Person_Birthday : ''),
			Person_Firname: (arguments[0].Person_Firname ? arguments[0].Person_Firname : ''),
			Person_Secname: (arguments[0].Person_Secname ? arguments[0].Person_Secname : ''),
			Person_Surname: (arguments[0].Person_Surname ? arguments[0].Person_Surname : ''),
			callback: function() {
				var field = base_form.findField('EvnUslugaDispOrp_setDate');
				clearDateAfterPersonDeath('personpanelid', 'eudo13ewPersonInformationFrame', field);
			}
		});

  		var loadMask = new Ext.LoadMask(Ext.get('EvnUslugaDispOrp13EditWindow'), { msg: LOAD_WAIT });
		loadMask.show();

        form.getForm().setValues(arguments[0].formParams);

		var sex_id = arguments[0].Sex_id;
		var age = arguments[0].Person_Age;
		
		var uslugacomplex_combo = current_window.findById('eudo13ewUslugaComplexCombo');
		
		var uslugacategorylist = ['nothing'];
		switch ( getRegionNick() ) {
			case 'perm':
			case 'astra':
			case 'buryatiya':
				uslugacategorylist.push('tfoms', 'gost2011');
			break;

			case 'kareliya':
				uslugacategorylist.push('tfoms');
			break;

			case 'ekb':
				uslugacategorylist.push('tfoms', 'gost2011');
			break;

			case 'kz':
				uslugacategorylist.push('classmedus');
			break;

			case 'pskov':
				uslugacategorylist.push('pskov_foms');
			break;

			default:
				uslugacategorylist.push('gost2011');
			break;
		}
		
		uslugacomplex_combo.getStore().baseParams['uslugaCategoryList'] = Ext.util.JSON.encode(uslugacategorylist);
		uslugacomplex_combo.getStore().baseParams['uslugaComplexCodeList'] = Ext.util.JSON.encode(this.uslugaComplexAllowed);
		uslugacomplex_combo.getStore().baseParams.UslugaComplex_Date = (typeof this.UslugaComplex_Date == 'object' ? Ext.util.Format.date(this.UslugaComplex_Date, 'd.m.Y') : this.UslugaComplex_Date);
		uslugacomplex_combo.getStore().baseParams.LpuSection_id = null;
		uslugacomplex_combo.getStore().baseParams.EvnPLDisp_id = this.EvnPLDisp_id;
		if (getRegionNick() == 'ufa') {
			base_form.findField('UslugaComplex_id').getStore().baseParams.filterByLpuLevel = 1;
		}

		// загрузить услуги в комбо, задисаблить комбо, если одна услуга
		base_form.findField('UslugaComplex_id').getStore().removeAll();
		this.lastUslugaComplexParams = null;
		
		var med_personal_id = arguments[0].formParams.MedPersonal_id || null;
		var lpu_section_id = arguments[0].formParams.LpuSection_id || null;
		
		this.age = arguments[0].Person_Age;
		this.Person_Birthday = arguments[0].Person_Birthday;
		
        switch (current_window.action)
        {
            case 'add':
                current_window.setTitle(lang['obsledovanie_dobavlenie']);
                current_window.enableEdit(true);
				current_window.loadUslugaComplexCombo();
				
				loadMask.hide();
				current_window.findById('eudo13ewEvnUslugaDispOrp_setDate').focus(false, 250);
				current_window.findById('eudo13ewExaminationPlaceCombo').setValue(1); //по умолчанию устанавливаем значение «В своем МУ»
				current_window.findById('eudo13ewExaminationPlaceCombo').fireEvent('change', current_window.findById('eudo13ewExaminationPlaceCombo'), current_window.findById('eudo13ewExaminationPlaceCombo').getValue());

				// @task https://redmine.swan.perm.ru//issues/109117
				if ( getRegionNick() == 'perm' && current_window.DispClass_id.toString().inlist([ '3', '7' ]) ) {
					base_form.findField('EvnUslugaDispOrp_Result').setValue('Выполнено');
				}
				break;

        	case 'edit':
			case 'view':
				if (current_window.action == 'edit') {
					current_window.setTitle(lang['obsledovanie_redaktirovanie']);
					current_window.enableEdit(true);
				} else {
					current_window.setTitle(lang['obsledovanie_prosmotr']);
					current_window.enableEdit(false);
				}
				current_window.findById('eudo13ewExaminationPlaceCombo').fireEvent('change', current_window.findById('eudo13ewExaminationPlaceCombo'), current_window.findById('eudo13ewExaminationPlaceCombo').getValue());

				var ExaminationPlace_id = current_window.findById('eudo13ewExaminationPlaceCombo').getValue();

				if (!Ext.isEmpty(ExaminationPlace_id) && ExaminationPlace_id == 3) {
					current_window.loadFirstMedPersonal = false;
				}

				var setDate = base_form.findField('EvnUslugaDispOrp_setDate').getValue();
				var setTime = base_form.findField('EvnUslugaDispOrp_setTime').getValue();
				var disDate = base_form.findField('EvnUslugaDispOrp_disDate').getValue();
				var disTime = base_form.findField('EvnUslugaDispOrp_disTime').getValue();

				if ((!Ext.isEmpty(disDate) || !Ext.isEmpty(disTime)) && (disDate-setDate != 0 || setTime != disTime)) {
					this.toggleVisibleDisDTPanel('show');
				}
				
				// устанавливаем врача
				var
					LpuSection_id = base_form.findField('LpuSection_id').getValue(),
					MedPersonal_id = base_form.findField('MedPersonal_id').getValue(),
					MedStaffFact_id = base_form.findField('MedStaffFact_id').getValue();

				if ( !Ext.isEmpty(MedPersonal_id) || !Ext.isEmpty(MedStaffFact_id) ) {
					var index = base_form.findField('MedStaffFact_id').getStore().findBy(function(rec) {
						return (Number(rec.get('MedStaffFact_id')) == Number(MedStaffFact_id));
					});

					if ( index == -1 ) {
						index = base_form.findField('MedStaffFact_id').getStore().findBy(function(rec) {
							return (Number(rec.get('MedPersonal_id')) == Number(MedPersonal_id) && Number(rec.get('LpuSection_id')) == Number(LpuSection_id));
						});
					}

					if ( index == -1 ) {
						index = base_form.findField('MedStaffFact_id').getStore().findBy(function(rec) {
							return (Number(rec.get('MedPersonal_id')) == Number(MedPersonal_id));
						});
					}

					var med_personal_record = base_form.findField('MedStaffFact_id').getStore().getAt(index);

					if ( med_personal_record ) {
						base_form.findField('MedStaffFact_id').setValue(med_personal_record.get('MedStaffFact_id'));
					}
				}

				current_window.loadUslugaComplexCombo();

				loadMask.hide();
				current_window.findById('eudo13ewEvnUslugaDispOrp_setDate').fireEvent('change', current_window.findById('eudo13ewEvnUslugaDispOrp_setDate'), current_window.findById('eudo13ewEvnUslugaDispOrp_setDate').getValue());
				current_window.findById('eudo13ewEvnUslugaDispOrp_setDate').focus(false, 250);
				break;
        }
    },
	setDisDT: function() {
		if ( this.isVisibleDisDTPanel ) {
			return false;
		}

		var base_form = this.findById('EvnUslugaDispOrp13EditForm').getForm();

		base_form.findField('EvnUslugaDispOrp_disDate').setValue(base_form.findField('EvnUslugaDispOrp_setDate').getValue());
		base_form.findField('EvnUslugaDispOrp_disTime').setValue(base_form.findField('EvnUslugaDispOrp_setTime').getValue());
	},
	toggleVisibleDisDTPanel: function(action)
	{
		var base_form = this.findById('EvnUslugaDispOrp13EditForm').getForm();

		if (action == 'show') {
			this.isVisibleDisDTPanel = false;
		} else if (action == 'hide') {
			this.isVisibleDisDTPanel = true;
		}

		if (this.isVisibleDisDTPanel) {
			this.findById('eudo13ew_EvnUslugaDisDTPanel').hide();
			this.findById('eudo13ew_ToggleVisibleDisDTBtn').setText(lang['utochnit_period_vyipolneniya']);
			base_form.findField('EvnUslugaDispOrp_disDate').setAllowBlank(true);
			base_form.findField('EvnUslugaDispOrp_disTime').setAllowBlank(true);
			base_form.findField('EvnUslugaDispOrp_disDate').setValue(null);
			base_form.findField('EvnUslugaDispOrp_disTime').setValue(null);
			base_form.findField('EvnUslugaDispOrp_disDate').setMaxValue(undefined);
			this.isVisibleDisDTPanel = false;
		} else {
			this.findById('eudo13ew_EvnUslugaDisDTPanel').show();
			this.findById('eudo13ew_ToggleVisibleDisDTBtn').setText(lang['skryit_polya']);
			base_form.findField('EvnUslugaDispOrp_disDate').setAllowBlank(false);
			base_form.findField('EvnUslugaDispOrp_disTime').setAllowBlank(false);
			base_form.findField('EvnUslugaDispOrp_disDate').setMaxValue(getGlobalOptions().date);
			this.isVisibleDisDTPanel = true;
		}
	},
    width: 700
});