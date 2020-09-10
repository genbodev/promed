/**
* swEvnUslugaDispOrp13SecEditWindow - окно редактирования/добавления выполнения лабораторного исследования по диспасеризации детей сирот детей-сирот
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
* @comment        Префикс для id компонентов EUDO13SEF (swEvnUslugaDispOrp13SecEditWindow)
*	                tabIndex: TABINDEX_EUDO13SEF = 9300
*
*
* Использует: окно редактирования талона по диспасеризации детей сирот (swEvnPLDispOrpEditWindow)
*/

sw.Promed.swEvnUslugaDispOrp13SecEditWindow = Ext.extend(sw.Promed.BaseForm, {
	action: null,
	autoHeight: true,
	buttonAlign: 'left',
    callback: Ext.emptyFn,
    closable: true,
    closeAction: 'hide',
    collapsible: true,
	doSave: function() {
		var add_flag = true;
		var current_window = this;
		var base_form = this.findById('EvnUslugaDispOrp13SecEditForm').getForm();
		var index = -1;
		var lpu_section_id = current_window.findById('EUDO13SEFLpuSectionCombo').getValue();
		var lpu_section_name = '';
		var med_staff_fact_id = current_window.findById('EUDO13SEFMedPersonalCombo').getValue();
		var med_personal_fio = '';
		var uslugacomplex_id = current_window.findById('EUDO13SEFUslugaComplexCombo').getValue();
		var uslugacomplex_code = '';
		var uslugacomplex_name = '';
		var ExaminationPlace_id = current_window.findById('EUDO13SEFExaminationPlaceCombo').getValue();
		var record_status = current_window.findById('EUDO13SEFRecord_Status').getValue();

		// Проверка на наличие у врача кода ДЛО или специальности https://redmine.swan.perm.ru/issues/47172
		// Проверку кода ДЛО убрали в https://redmine.swan.perm.ru/issues/118763
		if ( getRegionNick().inlist([ 'kareliya', 'penza' ]) ) {
			var MedSpecOms_id = base_form.findField('MedStaffFact_id').getFieldValue('MedSpecOms_id');

			if ( !Ext.isEmpty(med_staff_fact_id) && Ext.isEmpty(MedSpecOms_id) ) {
				sw.swMsg.alert(lang['soobschenie'], lang['u_vracha_ne_ukazana_spetsialnost'], function() { base_form.findField('MedStaffFact_id').clearValue(); } );
				return false;
			}
		}

		if (!current_window.findById('EvnUslugaDispOrp13SecEditForm').getForm().isValid())
		{
            Ext.MessageBox.show({
                buttons: Ext.Msg.OK,
                fn: function() {
                	current_window.findById('EUDO13SEFEvnUslugaDispOrp_setDate').focus(false);
                },
                icon: Ext.Msg.WARNING,
                msg: ERR_INVFIELDS_MSG,
                title: ERR_INVFIELDS_TIT
            });
            return false;
		}
		
		if ( current_window.findById('EUDO13SEFEvnUslugaDispOrp_setDate').getValue() > current_window.findById('EUDO13SEFEvnUslugaDispOrp_didDate').getValue() )
		{
            Ext.MessageBox.show({
                buttons: Ext.Msg.OK,
                fn: function() {
                	current_window.findById('EUDO13SEFEvnUslugaDispOrp_setDate').focus(false);
                },
                icon: Ext.Msg.WARNING,
                msg: lang['data_issledovaniya_ne_mojet_prevyishat_datu_polucheniya_rezultata'],
                title: lang['oshibka']
            });
            return false;
		}
		
		var set_date = current_window.findById('EUDO13SEFEvnUslugaDispOrp_setDate').getValue();
		var did_date = current_window.findById('EUDO13SEFEvnUslugaDispOrp_didDate').getValue();		
		if ( ( set_date.getMonthsBetween(did_date) > 3 ) || ( set_date.getMonthsBetween(did_date) == 3 && (set_date.getDate() != did_date.getDate()) ) )
		{
			Ext.MessageBox.show({
                buttons: Ext.Msg.OK,
                fn: function() {
                	current_window.findById('EUDO13SEFEvnUslugaDispOrp_setDate').focus(false);
                },
                icon: Ext.Msg.WARNING,
                msg: lang['data_polucheniya_rezultata_laboratornogo_issledovaniya_ne_bolee_3-h_mesyatsev_s_datyi_issledovaniya'],
                title: lang['oshibka']
            });
            return false;
		}
		
		var pl_set_date = current_window.set_date;
		var set_date = base_form.findField('EvnUslugaDispOrp_setDate').getValue();
		var set_time = base_form.findField('EvnUslugaDispOrp_setTime').getValue();
		var dis_date = base_form.findField('EvnUslugaDispOrp_disDate').getValue();
		var dis_time = base_form.findField('EvnUslugaDispOrp_disTime').getValue();

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
		
		if (record_status == 1)
		{
			record_status = 2;
		}

		index = current_window.findById('EUDO13SEFLpuSectionCombo').getStore().findBy(function(rec) { return rec.get('LpuSection_id') == lpu_section_id; });
		
		if (index >= 0)
		{
			lpu_section_name = current_window.findById('EUDO13SEFLpuSectionCombo').getStore().getAt(index).data.LpuSection_Name;
		}

		var med_personal_fio = '';
		var med_personal_id = null;
		
		var record = current_window.findById('EUDO13SEFMedPersonalCombo').getStore().getById(med_staff_fact_id);
		
		if ( record ) {
			med_personal_fio = record.get('MedPersonal_Fio');
			med_personal_id = record.get('MedPersonal_id');
		}

		index = current_window.findById('EUDO13SEFUslugaComplexCombo').getStore().findBy(function(rec) { return rec.get('UslugaComplex_id') == uslugacomplex_id; });
		if (index >= 0)
		{
			uslugacomplex_code = current_window.findById('EUDO13SEFUslugaComplexCombo').getStore().getAt(index).data.UslugaComplex_Code;
			uslugacomplex_name = current_window.findById('EUDO13SEFUslugaComplexCombo').getStore().getAt(index).data.UslugaComplex_Name;
		}
		
		index = current_window.findById('EUDO13SEFExaminationPlaceCombo').getStore().findBy(function(rec) { return rec.get('ExaminationPlace_id') == ExaminationPlace_id; });
		if (index >= 0)
		{
			ExaminationPlace_Name = current_window.findById('EUDO13SEFExaminationPlaceCombo').getStore().getAt(index).data.ExaminationPlace_Name;
		}
		
		if (current_window.action != 'add')
		{
			add_flag = false;
		}
		var data = [{
			'EvnUslugaDispOrp_id': current_window.findById('EUDO13SEFEvnUslugaDispOrp_id').getValue(),
			'EvnUslugaDispOrp_setDate': base_form.findField('EvnUslugaDispOrp_setDate').getValue(),
			'EvnUslugaDispOrp_setTime': base_form.findField('EvnUslugaDispOrp_setTime').getValue(),
			'EvnUslugaDispOrp_disDate': base_form.findField('EvnUslugaDispOrp_disDate').getValue(),
			'EvnUslugaDispOrp_disTime': base_form.findField('EvnUslugaDispOrp_disTime').getValue(),
			'EvnUslugaDispOrp_didDate': base_form.findField('EvnUslugaDispOrp_didDate').getValue(),
			'LpuSection_id': lpu_section_id,
			'Lpu_uid': base_form.findField('Lpu_uid').getValue(),
			'MedSpecOms_id': base_form.findField('MedSpecOms_id').getValue(),
			'LpuSectionProfile_id': base_form.findField('LpuSectionProfile_id').getValue(),
			'ExaminationPlace_id': ExaminationPlace_id,
			'ExaminationPlace_Name': ExaminationPlace_Name,
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
	id: 'EvnUslugaDispOrp13SecEditWindow',
    initComponent: function() {
		var win = this;
		
        Ext.apply(this, {
			buttons: [{
				handler: function() {
					win.doSave();
				},
				iconCls: 'save16',
				id: 'EUDO13SEFSaveButton',
				tabIndex: TABINDEX_EUDO13SEF + 15,
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
				id: 'EUDO13SEFCancelButton',
				onTabAction: function() {
					Ext.getCmp('EUDO13SEFEvnUslugaDispOrp_setDate').focus(true, 200);
				},
				onShiftTabAction: function() {
					Ext.getCmp('EUDO13SEFSaveButton').focus(true, 200);
				},
				tabIndex: TABINDEX_EUDO13SEF + 16,
				text: BTN_FRMCANCEL
			}],
            items: [
				new	sw.Promed.PersonInformationPanelShort({
					id: 'EUDO13SEFPersonInformationFrame',
					region: 'north'
				}),
				new Ext.form.FormPanel({
					autoHeight: true,
					bodyBorder: false,
					bodyStyle: 'padding: 5px 5px 0',
					border: false,
					frame: false,
					id: 'EvnUslugaDispOrp13SecEditForm',
					labelAlign: 'right',
					labelWidth: 180,
					items: [{
						id: 'EUDO13SEFEvnUslugaDispOrp_id',
						name: 'EvnUslugaDispOrp_id',
						value: 0,
						xtype: 'hidden'
					}, {
						id: 'EUDO13SEFRecord_Status',
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
								fieldLabel: lang['data_issledovaniya'],
								format: 'd.m.Y',
								id: 'EUDO13SEFEvnUslugaDispOrp_setDate',
								listeners: {
									'keydown':  function(inp, e) {
										if ( e.shiftKey && e.getKey() == Ext.EventObject.TAB )
										{
											e.stopEvent();
											Ext.getCmp('EUDO13SEFCancelButton').focus(true, 200);
										}
									},
									'change': function(field, newValue, oldValue) {
										if ( blockedDateAfterPersonDeath('personpanelid', 'EUDO13SEFPersonInformationFrame', field, newValue, oldValue) ) {
											return false;
										}

										var base_form = this.findById('EvnUslugaDispOrp13SecEditForm').getForm();

										if ( !Ext.isEmpty(newValue) && Ext.isEmpty(base_form.findField('EvnUslugaDispOrp_didDate').getValue()) ) {
											base_form.findField('EvnUslugaDispOrp_didDate').setValue(newValue);
										}

										this.filterLpuCombo();
										this.setLpuSectionAndMedStaffFactFilter();
										this.filterProfileAndMedSpec();

										var uslugacategory_combo = this.findById('EUDO13SEFUslugaCategoryCombo');
										if (newValue && getRegionNick() == 'perm') {
											if (newValue < new Date('2014-12-31')) {
												uslugacategory_combo.setFieldValue('UslugaCategory_SysNick', 'tfoms');
											} else {
												uslugacategory_combo.setFieldValue('UslugaCategory_SysNick', 'gost2011');
											}
											uslugacategory_combo.fireEvent('select', uslugacategory_combo, uslugacategory_combo.getStore().getById(uslugacategory_combo.getValue()));
										}

										this.setDisDT();
									}.createDelegate(this)
								},
								name: 'EvnUslugaDispOrp_setDate',
								maxValue: Date.parseDate(getGlobalOptions().date, 'd.m.Y'),
								plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
								tabIndex: TABINDEX_EUDO13SEF+1,
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
								id: 'EUDO13SEFEvnUslugaDispOrp_setTime',
								name: 'EvnUslugaDispOrp_setTime',
								onTriggerClick: function() {
									var base_form = this.findById('EvnUslugaDispOrp13SecEditForm').getForm();

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
								tabIndex: TABINDEX_EUDO13SEF + 2,
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
								id: 'EUDO13SEF_ToggleVisibleDisDTBtn',
								text: lang['utochnit_period_vyipolneniya'],
								handler: function() {
									this.toggleVisibleDisDTPanel();
								}.createDelegate(this)
							}]
						}]
					}, {
						border: false,
						layout: 'column',
						id: 'EUDO13SEF_EvnUslugaDisDTPanel',
						items: [{
							border: false,
							layout: 'form',
							labelWidth: 180,
							items: [{
								fieldLabel: lang['data_okonchaniya_vyipolneniya'],
								format: 'd.m.Y',
								id: 'EUDO13SEFEvnUslugaDispOrp_disDate',
								name: 'EvnUslugaDispOrp_disDate',
								plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
								tabIndex: TABINDEX_EUDO13SEF + 3,
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
								id: 'EUDO13SEFEvnUslugaDispOrp_disTime',
								name: 'EvnUslugaDispOrp_disTime',
								onTriggerClick: function() {
									var base_form = this.findById('EvnUslugaDispOrp13SecEditForm').getForm();

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
								tabIndex: TABINDEX_EUDO13SEF + 4,
								validateOnBlur: false,
								width: 60,
								xtype: 'swtimefield'
							}]
						}, {
							layout: 'form',
							border: false,
							items: [{
								xtype: 'button',
								id: 'EUDO13SEF_DTCopyBtn',
								text: '=',
								handler: function() {
									var base_form = this.findById('EvnUslugaDispOrp13SecEditForm').getForm();

									base_form.findField('EvnUslugaDispOrp_disDate').setValue(base_form.findField('EvnUslugaDispOrp_setDate').getValue());
									base_form.findField('EvnUslugaDispOrp_disTime').setValue(base_form.findField('EvnUslugaDispOrp_setTime').getValue());
								}.createDelegate(this)
							}]
						}]
					}, {
						allowBlank: false,
						fieldLabel: lang['data_rezultata'],
						format: 'd.m.Y',
						id: 'EUDO13SEFEvnUslugaDispOrp_didDate',
						name: 'EvnUslugaDispOrp_didDate',
						maxValue: Date.parseDate(getGlobalOptions().date, 'd.m.Y'),
						plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
						tabIndex: TABINDEX_EUDO13SEF + 5,
						width: 100,
						xtype: 'swdatefield'
					}, {
						allowBlank: false,
						enableKeyEvents: true,
						id: 'EUDO13SEFExaminationPlaceCombo',
						listeners: {
							'change': function(field, newValue, oldValue) {
								var base_form = win.findById('EvnUslugaDispOrp13SecEditForm').getForm();
								
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

								if (getRegionNick() == 'buryatiya') {
									base_form.findField('UslugaComplex_id').clearValue();
									base_form.findField('UslugaComplex_id').getStore().removeAll();
									base_form.findField('UslugaComplex_id').lastQuery = 'This query sample that is not will never appear';
									base_form.findField('UslugaComplex_id').getStore().baseParams.ExaminationPlace_id = newValue;
								}
							}.createDelegate(this)
						},
						name: 'ExaminationPlace_id',
						tabIndex: TABINDEX_EUDO13SEF + 6,
						validateOnBlur: false,
						width: 350,
						xtype: 'swexaminationplacecombo'
					}, {
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
						tabIndex: TABINDEX_EUDO13SEF + 7,
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
						tabIndex: TABINDEX_EUDO13SEF + 8,
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
						tabIndex: TABINDEX_EUDO13SEF + 9,
						width: 450,
						hiddenName: 'MedSpecOms_id',
						listeners: {
							'change': function(field, newValue, oldValue) {
								win.setLpuSectionAndMedStaffFactFilter();
							}
						}
					}, {
						allowBlank: true,
						hiddenName: 'LpuSection_id',
						id: 'EUDO13SEFLpuSectionCombo',
						lastQuery: '',
						listWidth: 650,
						linkedElements: [
							'EUDO13SEFMedPersonalCombo'
						],
						tabIndex: TABINDEX_EUDO13SEF + 10,
						width: 450,
						xtype: 'swlpusectionglobalcombo'
					}, {
						allowBlank: true,
						hiddenName: 'MedStaffFact_id',
						id: 'EUDO13SEFMedPersonalCombo',
						lastQuery: '',
						listWidth: 650,
						parentElementId: 'EUDO13SEFLpuSectionCombo',
						listeners: {
							'change': function(field, newValue, oldValue) {
								if ( getRegionNick().inlist([ 'kareliya', 'penza' ]) && !Ext.isEmpty(newValue) ) {
									var index = field.getStore().findBy(function(rec) {
										return (rec.get('MedStaffFact_id') == newValue);
									});

									if ( index >= 0 ) {
										var
											MedSpecOms_id = field.getStore().getAt(index).get('MedSpecOms_id'),
											MedPersonal_Snils = field.getStore().getAt(index).get('Person_Snils');

										if ( Ext.isEmpty(MedSpecOms_id) ) {
											sw.swMsg.alert(lang['soobschenie'], lang['u_vracha_ne_ukazana_spetsialnost'], function() { field.clearValue(); } );
											return false;
										}
										else if ( Ext.isEmpty(MedPersonal_Snils) ) {
											sw.swMsg.alert(lang['soobschenie'], lang['u_vracha_ne_ukazan_snils'], function() { field.clearValue(); } );
											return false;
										}
									}
								}
							}
						},
						tabIndex: TABINDEX_EUDO13SEF + 11,
						width: 450,
						xtype: 'swmedstafffactglobalcombo'
					}, {
						layout: 'form',
						hidden: (!getRegionNick().inlist(['perm', 'ufa', 'adygeya', 'ekb', 'khak', 'vologda'])),
						border: false,
						items: [{
							allowBlank: (!getRegionNick().inlist(['perm', 'ufa', 'adygeya', 'ekb', 'khak', 'vologda'])),
							id: 'EUDO13SEFUslugaCategoryCombo',
							fieldLabel: lang['kategoriya_uslugi'],
							hiddenName: 'UslugaCategory_id',
							listeners: {
								'select': function (combo, record) {
									var usluga_combo = win.findById('EUDO13SEFUslugaComplexCombo');

									usluga_combo.clearValue();
									usluga_combo.getStore().removeAll();

									if ( !record ) {
										usluga_combo.setUslugaCategoryList();
										return false;
									}

									usluga_combo.setUslugaCategoryList([ record.get('UslugaCategory_SysNick') ]);

									return true;
								}
							},
							listWidth: 400,
							tabIndex: TABINDEX_EUDO13SEF + 12,
							width: 250,
							xtype: 'swuslugacategorycombo'
						}]
					}, {
						allowBlank: false,
						id: 'EUDO13SEFUslugaComplexCombo',
						fieldLabel: langs('Услуга'),
						hiddenName: 'UslugaComplex_id',
						listWidth: 500,
						tabIndex: TABINDEX_EUDO13SEF + 13,
						width: 450,
						nonDispOnly: false,
						xtype: 'swuslugacomplexnewcombo'
					}, {
						allowBlank: true,
						fieldLabel: 'Результат',
						tabIndex: TABINDEX_EUDO13SEF + 14,
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
		
    	sw.Promed.swEvnUslugaDispOrp13SecEditWindow.superclass.initComponent.apply(this, arguments);
		
		this.findById('EUDO13SEFUslugaComplexCombo').addListener('change', function(combo, newValue, oldValue) {
			this.filterProfileAndMedSpec();
			this.setLpuSectionAndMedStaffFactFilter();
		}.createDelegate(this));
    },
	filterProfileAndMedSpec: function() {
		var win = this;
		var base_form = this.findById('EvnUslugaDispOrp13SecEditForm').getForm();
		
		if (getRegionNick() == 'ekb') {
			win.MedSpecOms_id = base_form.findField('UslugaComplex_id').getFieldValue('MedSpecOms_id');
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

        	var current_window = Ext.getCmp('EvnUslugaDispOrp13SecEditWindow');

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
		var base_form = this.findById('EvnUslugaDispOrp13SecEditForm').getForm();
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
	setLpuSectionAndMedStaffFactFilter: function() {
		var win = this;
		var base_form = this.findById('EvnUslugaDispOrp13SecEditForm').getForm();

		// Учитываем дату и место выполнения
		var EvnUslugaDispOrp_setDate = base_form.findField('EvnUslugaDispOrp_setDate').getValue();
		var ExaminationPlace_id = base_form.findField('ExaminationPlace_id').getValue();

		/*base_form.findField('LpuSection_id').setAllowBlank(false);
		base_form.findField('MedStaffFact_id').setAllowBlank(false);*/
		
		if ( !Ext.isEmpty(ExaminationPlace_id) && ExaminationPlace_id == 3 ) {
			// показать поля МО, Профиль, Специальность
			base_form.findField('Lpu_uid').showContainer();
			if (!getRegionNick().inlist(['buryatiya', 'adygeya'])) {
				base_form.findField('LpuSectionProfile_id').showContainer();
				base_form.findField('MedSpecOms_id').showContainer();
			}
			base_form.findField('LpuSection_id').disableLinkedElements();
			base_form.findField('MedStaffFact_id').disableParentElement();

			base_form.findField('Lpu_uid').setAllowBlank(getRegionNick().inlist([ 'pskov', 'ufa', 'ekb' ]));
			base_form.findField('LpuSectionProfile_id').setAllowBlank(getRegionNick().inlist([ 'pskov', 'ufa', 'ekb', 'buryatiya', 'adygeya' ]));
			base_form.findField('MedSpecOms_id').setAllowBlank(getRegionNick().inlist([ 'pskov', 'ufa', 'ekb', 'buryatiya', 'adygeya' ]));

			base_form.findField('LpuSection_id').setAllowBlank(getRegionNick().inlist([ 'krym' ]));
			base_form.findField('MedStaffFact_id').setAllowBlank(getRegionNick().inlist([ 'krym' ]));

			if (!getRegionNick().inlist(['buryatiya', 'adygeya'])) {
				if (Ext.isEmpty(base_form.findField('LpuSectionProfile_id').getValue()) || Ext.isEmpty(base_form.findField('Lpu_uid').getValue())) {
					base_form.findField('LpuSection_id').getStore().removeAll();
					base_form.findField('LpuSection_id').clearValue();
					win.lastLpuSectionProfile_id = base_form.findField('LpuSectionProfile_id').getValue();
					win.lastLpu_uid1 = base_form.findField('Lpu_uid').getValue();
				}

				if (Ext.isEmpty(base_form.findField('MedSpecOms_id').getValue()) || Ext.isEmpty(base_form.findField('Lpu_uid').getValue())) {
					base_form.findField('MedStaffFact_id').getStore().removeAll();
					base_form.findField('MedStaffFact_id').clearValue();
					win.lastMedSpecOms_id = base_form.findField('MedSpecOms_id').getValue();
					win.lastLpu_uid2 = base_form.findField('Lpu_uid').getValue();
				}

				if (
					!Ext.isEmpty(base_form.findField('LpuSectionProfile_id').getValue()) && !Ext.isEmpty(base_form.findField('Lpu_uid').getValue()) &&
					(base_form.findField('Lpu_uid').getValue() != win.lastLpu_uid1 || base_form.findField('LpuSectionProfile_id').getValue() != win.lastLpuSectionProfile_id)
				) {
					win.lastLpuSectionProfile_id = base_form.findField('LpuSectionProfile_id').getValue();
					win.lastLpu_uid1 = base_form.findField('Lpu_uid').getValue();

					base_form.findField('LpuSection_id').getStore().load({
						callback: function () {
							var index = base_form.findField('LpuSection_id').getStore().findBy(function (rec) {
								return (rec.get('LpuSection_id') == base_form.findField('LpuSection_id').getValue());
							});

							if (base_form.findField('LpuSection_id').getStore().getCount() == 1) {
								ucid = base_form.findField('LpuSection_id').getStore().getAt(0).get('LpuSection_id');
								base_form.findField('LpuSection_id').setValue(ucid);
							} else if (base_form.findField('LpuSection_id').getStore().getCount() > 1) {
								if (index >= 0) {
									ucid = base_form.findField('LpuSection_id').getStore().getAt(index).get('LpuSection_id');
								} else {
									ucid = base_form.findField('LpuSection_id').getStore().getAt(0).get('LpuSection_id');
								}
								base_form.findField('LpuSection_id').setValue(ucid);
							} else {
								base_form.findField('LpuSection_id').clearValue();
							}
						}.createDelegate(this),
						params: {
							LpuSectionProfile_id: base_form.findField('LpuSectionProfile_id').getValue(),
							Lpu_id: base_form.findField('Lpu_uid').getValue(),
							mode: 'combo'
						}
					});
				}

				if (
					!Ext.isEmpty(base_form.findField('MedSpecOms_id').getValue()) && !Ext.isEmpty(base_form.findField('Lpu_uid').getValue()) &&
					(base_form.findField('Lpu_uid').getValue() != win.lastLpu_uid2 || base_form.findField('MedSpecOms_id').getValue() != win.lastMedSpecOms_id)
				) {
					win.lastMedSpecOms_id = base_form.findField('MedSpecOms_id').getValue();
					win.lastLpu_uid2 = base_form.findField('Lpu_uid').getValue();

					base_form.findField('MedStaffFact_id').getStore().load({
						callback: function () {
							var index = base_form.findField('MedStaffFact_id').getStore().findBy(function (rec) {
								return (rec.get('MedPersonal_id') == base_form.findField('MedPersonal_id').getValue());
							});

							if (base_form.findField('MedStaffFact_id').getStore().getCount() == 1) {
								ucid = base_form.findField('MedStaffFact_id').getStore().getAt(0).get('MedStaffFact_id');
								base_form.findField('MedStaffFact_id').setValue(ucid);
							} else if (base_form.findField('MedStaffFact_id').getStore().getCount() > 1) {
								if (index >= 0) {
									ucid = base_form.findField('MedStaffFact_id').getStore().getAt(index).get('MedStaffFact_id');
								} else {
									ucid = base_form.findField('MedStaffFact_id').getStore().getAt(0).get('MedStaffFact_id');
								}
								base_form.findField('MedStaffFact_id').setValue(ucid);
							} else {
								base_form.findField('MedStaffFact_id').clearValue();
							}
						}.createDelegate(this),
						params: {
							mode: 'combo',
							MedSpecOms_id: base_form.findField('MedSpecOms_id').getValue(),
							Lpu_id: base_form.findField('Lpu_uid').getValue()
						}
					});
				}
			} else {
				if (Ext.isEmpty(base_form.findField('Lpu_uid').getValue())) {
					base_form.findField('LpuSection_id').getStore().removeAll();
					base_form.findField('LpuSection_id').clearValue();
					win.lastLpu_uid1 = base_form.findField('Lpu_uid').getValue();
					base_form.findField('MedStaffFact_id').getStore().removeAll();
					base_form.findField('MedStaffFact_id').clearValue();
					win.lastLpu_uid2 = base_form.findField('Lpu_uid').getValue();
				}

				if (
					!Ext.isEmpty(base_form.findField('Lpu_uid').getValue()) && base_form.findField('Lpu_uid').getValue() != win.lastLpu_uid1
				) {
					win.lastLpu_uid1 = base_form.findField('Lpu_uid').getValue();

					base_form.findField('LpuSection_id').getStore().load({
						callback: function () {
							var index = base_form.findField('LpuSection_id').getStore().findBy(function (rec) {
								return (rec.get('LpuSection_id') == base_form.findField('LpuSection_id').getValue());
							});

							if (base_form.findField('LpuSection_id').getStore().getCount() == 1) {
								ucid = base_form.findField('LpuSection_id').getStore().getAt(0).get('LpuSection_id');
								base_form.findField('LpuSection_id').setValue(ucid);
							} else if (base_form.findField('LpuSection_id').getStore().getCount() > 1) {
								if (index >= 0) {
									ucid = base_form.findField('LpuSection_id').getStore().getAt(index).get('LpuSection_id');
								} else {
									ucid = base_form.findField('LpuSection_id').getStore().getAt(0).get('LpuSection_id');
								}
								base_form.findField('LpuSection_id').setValue(ucid);
							} else {
								base_form.findField('LpuSection_id').clearValue();
							}
						}.createDelegate(this),
						params: {
							Lpu_id: base_form.findField('Lpu_uid').getValue(),
							mode: 'combo'
						}
					});
				}

				if (
					!Ext.isEmpty(base_form.findField('Lpu_uid').getValue()) && base_form.findField('Lpu_uid').getValue() != win.lastLpu_uid2
				) {
					win.lastLpu_uid2 = base_form.findField('Lpu_uid').getValue();

					base_form.findField('MedStaffFact_id').getStore().load({
						callback: function () {
							var index = base_form.findField('MedStaffFact_id').getStore().findBy(function (rec) {
								return (rec.get('MedPersonal_id') == base_form.findField('MedPersonal_id').getValue());
							});

							if (base_form.findField('MedStaffFact_id').getStore().getCount() == 1) {
								ucid = base_form.findField('MedStaffFact_id').getStore().getAt(0).get('MedStaffFact_id');
								base_form.findField('MedStaffFact_id').setValue(ucid);
							} else if (base_form.findField('MedStaffFact_id').getStore().getCount() > 1) {
								if (index >= 0) {
									ucid = base_form.findField('MedStaffFact_id').getStore().getAt(index).get('MedStaffFact_id');
								} else {
									ucid = base_form.findField('MedStaffFact_id').getStore().getAt(0).get('MedStaffFact_id');
								}
								base_form.findField('MedStaffFact_id').setValue(ucid);
							} else {
								base_form.findField('MedStaffFact_id').clearValue();
							}
						}.createDelegate(this),
						params: {
							mode: 'combo',
							Lpu_id: base_form.findField('Lpu_uid').getValue()
						}
					});
				}
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
			base_form.findField('LpuSection_id').enableLinkedElements();
			base_form.findField('MedStaffFact_id').enableParentElement();

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

			if (getRegionNick() === 'pskov')
			{
				var UslugaComplex_id = base_form.findField('UslugaComplex_id').getValue();
				if (UslugaComplex_id && EvnUslugaDispOrp_setDate)
				{
					params.UslugaComplex_MedSpecOms = {
						UslugaComplex_id: UslugaComplex_id,
						didDate: Ext.util.Format.date(EvnUslugaDispOrp_setDate, 'd.m.Y')
					};
				}
			}

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
		}

		this.syncSize();
		this.syncShadow();
	},
    show: function() {
		sw.Promed.swEvnUslugaDispOrp13SecEditWindow.superclass.show.apply(this, arguments);

		var current_window = this;

		current_window.restore();
		current_window.center();

        var form = current_window.findById('EvnUslugaDispOrp13SecEditForm');
		var base_form = form.getForm();
		form.getForm().reset();

       	current_window.callback = Ext.emptyFn;
       	current_window.onHide = Ext.emptyFn;
		current_window.ownerWindow = null;
		
        if (!arguments[0] || !arguments[0].formParams || !arguments[0].ownerWindow || !arguments[0].DispClass_id)
        {
        	Ext.Msg.alert(lang['soobschenie'], lang['nevernyie_parametryi'], function() { current_window.hide(); } );
        	return false;
        }
		
		this.lastLpuSectionProfile_id = null;
		this.lastLpu_uid1 = null;
		this.lastLpu_uid2 = null;
		this.lastMedSpecOms_id = null;
		this.DispClass_id = arguments[0].DispClass_id;
		this.isVisibleDisDTPanel = false;

		this.toggleVisibleDisDTPanel('hide');
		
		this.setLpuSectionAndMedStaffFactFilter();

		base_form.findField('ExaminationPlace_id').lastQuery = '';
		base_form.findField('ExaminationPlace_id').getStore().filterBy(function(rec) {
			return rec.get('ExaminationPlace_Code').toString().inlist([ '1', '3' ]);
		});

        if (arguments[0].action)
        {
        	current_window.action = arguments[0].action;
        }
		
		if (arguments[0].set_date)
        {
        	current_window.set_date = arguments[0].set_date;
        }

		this.MedSpecOms_id = null;
		
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
		
		this.usedUslugaComplexCodeList = [];
		if ( arguments[0].formParams.usedUslugaComplexCodeList )
        {
        	current_window.usedUslugaComplexCodeList = arguments[0].formParams.usedUslugaComplexCodeList;			
        }

		this.UslugaComplex_Date = null;
		if (arguments[0].UslugaComplex_Date)
		{
			this.UslugaComplex_Date = arguments[0].UslugaComplex_Date;
		}

		current_window.findById('EUDO13SEFPersonInformationFrame').load({
			Person_id: (arguments[0].Person_id ? arguments[0].Person_id : ''),
			Person_Birthday: (arguments[0].Person_Birthday ? arguments[0].Person_Birthday : ''),
			Person_Firname: (arguments[0].Person_Firname ? arguments[0].Person_Firname : ''),
			Person_Secname: (arguments[0].Person_Secname ? arguments[0].Person_Secname : ''),
			Person_Surname: (arguments[0].Person_Surname ? arguments[0].Person_Surname : ''),
			callback: function() {
				var field = base_form.findField('EvnUslugaDispOrp_setDate');
				clearDateAfterPersonDeath('personpanelid', 'EUDO13SEFPersonInformationFrame', field);
			}
		});

  		var loadMask = new Ext.LoadMask(Ext.get('EvnUslugaDispOrp13SecEditWindow'), { msg: LOAD_WAIT });
		loadMask.show();

        form.getForm().setValues(arguments[0].formParams);

		var sex_id = arguments[0].Sex_id;
		var age = arguments[0].Person_Age;
		
		var uslugacategory_combo = current_window.findById('EUDO13SEFUslugaCategoryCombo');
		var uslugacomplex_combo = current_window.findById('EUDO13SEFUslugaComplexCombo');

		var uslugacategorylist = ['nothing'];
		switch ( getRegionNick() ) {
			//case 'perm':
			case 'buryatiya':
				uslugacategorylist.push('tfoms');
			break;

			case 'kz':
				uslugacategorylist.push('classmedus');
			break;

			case 'ekb':
				uslugacategorylist.push('tfoms');
				uslugacategorylist.push('gost2011');
			break;

			default:
				uslugacategorylist.push('gost2011');
				break;
		}
		if (getRegionNick() != 'perm') {
			uslugacomplex_combo.getStore().baseParams['uslugaCategoryList'] = Ext.util.JSON.encode(uslugacategorylist);
		}
		// фильтрация для ддс 2 этап 
		uslugacomplex_combo.getStore().baseParams['DispFilter'] = "DispOrp13SecUsluga";
		uslugacomplex_combo.getStore().baseParams['DispClass_id'] = this.DispClass_id;
		uslugacomplex_combo.getStore().baseParams['disallowedUslugaComplexCodeList'] = Ext.util.JSON.encode(this.usedUslugaComplexCodeList);
		uslugacomplex_combo.getStore().baseParams.UslugaComplex_Date = (typeof this.UslugaComplex_Date == 'object' ? Ext.util.Format.date(this.UslugaComplex_Date, 'd.m.Y') : this.UslugaComplex_Date);
		uslugacomplex_combo.getStore().load();
		uslugacomplex_combo.fireEvent('change', uslugacomplex_combo, uslugacomplex_combo.getValue());
		
		var med_personal_id = arguments[0].formParams.MedPersonal_id || null;
		var lpu_section_id = arguments[0].formParams.LpuSection_id || null;
		
		this.age = arguments[0].Person_Age;
		this.Person_Birthday = arguments[0].Person_Birthday;
		
        switch (current_window.action)
        {
            case 'add':
                current_window.setTitle(lang['obsledovanie_dobavlenie']);
                current_window.enableEdit(true);
				
				loadMask.hide();
				current_window.findById('EUDO13SEFEvnUslugaDispOrp_setDate').focus(false, 250);
				current_window.findById('EUDO13SEFExaminationPlaceCombo').setValue(1); //по умолчанию устанавливаем значение «В своем МУ»
				current_window.findById('EUDO13SEFExaminationPlaceCombo').fireEvent('change', current_window.findById('EUDO13SEFExaminationPlaceCombo'), current_window.findById('EUDO13SEFExaminationPlaceCombo').getValue());

				if (getRegionNick() == 'perm') {
					var date1 = Date.parseDate(getGlobalOptions().date, 'd.m.Y');
					var date2 = new Date('2014-12-31');
					if (!Ext.isEmpty(base_form.findField('EvnUslugaDispOrp_setDate').getValue())) {
						date1 = base_form.findField('EvnUslugaDispOrp_setDate').getValue();
					}
					if (date1 < date2) {
						uslugacategory_combo.setFieldValue('UslugaCategory_SysNick', 'tfoms');
					} else {
						uslugacategory_combo.setFieldValue('UslugaCategory_SysNick', 'gost2011');
					}
					uslugacategory_combo.fireEvent('select', uslugacategory_combo, uslugacategory_combo.getStore().getById(uslugacategory_combo.getValue()));
				}
				else if(getRegionNick().inlist(['perm', 'ufa', 'adygeya', 'ekb', 'khak', 'vologda'])) {
					uslugacategory_combo.setFieldValue('UslugaCategory_SysNick', 'gost2011');
					uslugacategory_combo.fireEvent('select', uslugacategory_combo, uslugacategory_combo.getStore().getById(uslugacategory_combo.getValue()));
				}

				// @task https://redmine.swan.perm.ru//issues/109117
				if ( getRegionNick() == 'perm' && current_window.DispClass_id.toString().inlist([ '4', '8' ]) ) {
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
				current_window.findById('EUDO13SEFExaminationPlaceCombo').fireEvent('change', current_window.findById('EUDO13SEFExaminationPlaceCombo'), current_window.findById('EUDO13SEFExaminationPlaceCombo').getValue());

				var setDate = base_form.findField('EvnUslugaDispOrp_setDate').getValue();
				var setTime = base_form.findField('EvnUslugaDispOrp_setTime').getValue();
				var disDate = base_form.findField('EvnUslugaDispOrp_disDate').getValue();
				var disTime = base_form.findField('EvnUslugaDispOrp_disTime').getValue();

				if ((!Ext.isEmpty(disDate) || !Ext.isEmpty(disTime)) && (disDate-setDate != 0 || setTime != disTime)) {
					this.toggleVisibleDisDTPanel('show');
				}
				
				var uslugacomplex_id = uslugacomplex_combo.getValue();
				if (uslugacomplex_id != null && uslugacomplex_id.toString().length > 0)
				{
					uslugacomplex_combo.getStore().load({
						callback: function() {
							uslugacomplex_combo.getStore().each(function(record) {
								if (record.data.UslugaComplex_id == uslugacomplex_id)
								{
									uslugacomplex_combo.setValue(uslugacomplex_id);
									uslugacategory_combo.setValue(uslugacomplex_combo.getFieldValue('UslugaCategory_id'));
									uslugacomplex_combo.fireEvent('change', uslugacomplex_combo, uslugacomplex_combo.getValue());
								}
							});
						},
						params: { UslugaComplex_id: uslugacomplex_id }
					});
				}
				
				// устанавливаем врача
				var med_personal_id = base_form.findField('MedPersonal_id').getValue();
				var LpuSection_id = base_form.findField('LpuSection_id').getValue();
				if (!Ext.isEmpty(med_personal_id)) {
					var index = base_form.findField('MedStaffFact_id').getStore().findBy(function(rec) {
						if ( Number(rec.get('MedPersonal_id')) == Number(med_personal_id) && Number(rec.get('LpuSection_id')) == Number(LpuSection_id) ) {
							return true;
						}
						else {
							return false;
						}
					});
					var med_personal_record = base_form.findField('MedStaffFact_id').getStore().getAt(index);

					if ( med_personal_record ) {
						base_form.findField('MedStaffFact_id').setValue(med_personal_record.get('MedStaffFact_id'));
					}
				}
				
				loadMask.hide();
				current_window.findById('EUDO13SEFEvnUslugaDispOrp_setDate').fireEvent('change', current_window.findById('EUDO13SEFEvnUslugaDispOrp_setDate'), current_window.findById('EUDO13SEFEvnUslugaDispOrp_setDate').getValue());
				current_window.findById('EUDO13SEFEvnUslugaDispOrp_setDate').focus(false, 250);
				break;
        }
    },
	setDisDT: function() {
		if ( this.isVisibleDisDTPanel ) {
			return false;
		}

		var base_form = this.findById('EvnUslugaDispOrp13SecEditForm').getForm();

		base_form.findField('EvnUslugaDispOrp_disDate').setValue(base_form.findField('EvnUslugaDispOrp_setDate').getValue());
		base_form.findField('EvnUslugaDispOrp_disTime').setValue(base_form.findField('EvnUslugaDispOrp_setTime').getValue());
	},
	toggleVisibleDisDTPanel: function(action)
	{
		var base_form = this.findById('EvnUslugaDispOrp13SecEditForm').getForm();

		if (action == 'show') {
			this.isVisibleDisDTPanel = false;
		} else if (action == 'hide') {
			this.isVisibleDisDTPanel = true;
		}

		if (this.isVisibleDisDTPanel) {
			this.findById('EUDO13SEF_EvnUslugaDisDTPanel').hide();
			this.findById('EUDO13SEF_ToggleVisibleDisDTBtn').setText(lang['utochnit_period_vyipolneniya']);
			base_form.findField('EvnUslugaDispOrp_disDate').setAllowBlank(true);
			base_form.findField('EvnUslugaDispOrp_disTime').setAllowBlank(true);
			base_form.findField('EvnUslugaDispOrp_disDate').setValue(null);
			base_form.findField('EvnUslugaDispOrp_disTime').setValue(null);
			base_form.findField('EvnUslugaDispOrp_disDate').setMaxValue(undefined);
			this.isVisibleDisDTPanel = false;
		} else {
			this.findById('EUDO13SEF_EvnUslugaDisDTPanel').show();
			this.findById('EUDO13SEF_ToggleVisibleDisDTBtn').setText(lang['skryit_polya']);
			base_form.findField('EvnUslugaDispOrp_disDate').setAllowBlank(false);
			base_form.findField('EvnUslugaDispOrp_disTime').setAllowBlank(false);
			base_form.findField('EvnUslugaDispOrp_disDate').setMaxValue(getGlobalOptions().date);
			this.isVisibleDisDTPanel = true;
		}
	},
    width: 700
});