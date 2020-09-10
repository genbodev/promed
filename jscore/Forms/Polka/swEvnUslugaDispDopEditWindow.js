/**
* swEvnUslugaDispDopEditWindow - окно редактирования/добавления выполнения лабораторного исследования по доп. диспансеризации
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package         Polka
* @access          public
* @copyright       Copyright (c) 2009 Swan Ltd.
* @author          Ivan Petukhov aka Lich (megatherion@list.ru)
* @originalauthor  Stas Bykov aka Savage (savage1981@gmail.com)
* @version         6.07.2009
* @comment         Префикс для id компонентов EUDDEW (swEvnUslugaDispDopEditWindow)
*                  tabIndex: TABINDEX_EUDDEW (2800)
*
*
* Использует: окно редактирования талона по доп. диспансеризации (swEvnPLDispDopEditWindow)
*/

sw.Promed.swEvnUslugaDispDopEditWindow = Ext.extend(sw.Promed.BaseForm, {
	action: null,
	buttonAlign: 'left',
	buttons: [{
		handler: function() {
			this.ownerCt.doSave();
		},
		iconCls: 'save16',
		id: 'EUDDEW_SaveButton',
		tabIndex: TABINDEX_EUDDEW+8,
		text: BTN_FRMSAVE
	}, {
		handler: function() {
			this.ownerCt.hide();
		},
		iconCls: 'cancel16',
		id: 'EUDDEW_CancelButton',
		onTabAction: function() {
			Ext.getCmp('EUDDEW_EvnUslugaDispDop_setDate').focus(true, 200);
		},
		onShiftTabAction: function() {
			Ext.getCmp('EUDDEW_SaveButton').focus(true, 200);
		},
		tabIndex: TABINDEX_EUDDEW+9,
		text: BTN_FRMCANCEL
	}, 
		HelpButton(this, TABINDEX_EUDDEW+10)
	],
    callback: Ext.emptyFn,
    closable: true,
    closeAction: 'hide',
    collapsible: true,
	doSave: function() {
		var add_flag = true;
		var current_window = this;
		var index = -1;
		var dopdispuslugatype_id = current_window.findById('EUDDEW_DopDispUslugaTypeCombo').getValue();
		var dopdispuslugatype_name = '';
		var lpu_section_id = current_window.findById('EUDDEW_LpuSectionCombo').getValue();
		var lpu_section_name = '';
		var med_staff_fact_id = current_window.findById('EUDDEW_MedPersonalCombo').getValue();
		var med_personal_fio = '';
		var usluga_id = current_window.findById('EUDDEW_UslugaCombo').getValue();
		var usluga_code = '';
		var usluga_name = '';
		var examination_place_id = current_window.findById('EUDDEW_ExaminationPlaceCombo').getValue();
		var record_status = current_window.findById('EUDDEW_Record_Status').getValue();
			
		if (!current_window.findById('EvnUslugaDispDopEditForm').getForm().isValid())
		{
            Ext.MessageBox.show({
                buttons: Ext.Msg.OK,
                fn: function() {
                	current_window.findById('EUDDEW_EvnUslugaDispDop_setDate').focus(false);
                },
                icon: Ext.Msg.WARNING,
                msg: ERR_INVFIELDS_MSG,
                title: ERR_INVFIELDS_TIT
            });
            return false;
		}
		
		if ( current_window.findById('EUDDEW_EvnUslugaDispDop_setDate').getValue() > current_window.findById('EUDDEW_EvnUslugaDispDop_didDate').getValue() )
		{
            Ext.MessageBox.show({
                buttons: Ext.Msg.OK,
                fn: function() {
                	current_window.findById('EUDDEW_EvnUslugaDispDop_setDate').focus(false);
                },
                icon: Ext.Msg.WARNING,
                msg: lang['data_issledovaniya_ne_mojet_prevyishat_datu_polucheniya_rezultata'],
                title: lang['oshibka']
            });
            return false;
		}
		
		var set_date = current_window.findById('EUDDEW_EvnUslugaDispDop_setDate').getValue();
		var did_date = current_window.findById('EUDDEW_EvnUslugaDispDop_didDate').getValue();		
		if ( ( set_date.getMonthsBetween(did_date) > 3 ) || ( set_date.getMonthsBetween(did_date) == 3 && (set_date.getDate() != did_date.getDate()) ) )
		{
			Ext.MessageBox.show({
                buttons: Ext.Msg.OK,
                fn: function() {
                	current_window.findById('EUDDEW_EvnUslugaDispDop_setDate').focus(false);
                },
                icon: Ext.Msg.WARNING,
                msg: lang['data_polucheniya_rezultata_laboratornogo_issledovaniya_ne_bolee_3-h_mesyatsev_s_datyi_issledovaniya'],
                title: lang['oshibka']
            });
            return false;
		}
		
		/* убрал проверку, так как надо проверять при сохранении талона
		var pl_set_date = current_window.set_date;
		var set_date = current_window.findById('EUDDEW_EvnUslugaDispDop_setDate').getValue();
		var usluga_type_id = Ext.getCmp('EUDDEW_DopDispUslugaTypeCombo').getValue();
		if ( pl_set_date > set_date && (usluga_type_id == 6 || usluga_type_id == 5) )
		{
			if ( ( set_date.getMonthsBetween(pl_set_date) > 24 ) || ( set_date.getMonthsBetween(pl_set_date) == 24 && (set_date.getDate() != pl_set_date.getDate()) ) )
			{
				Ext.MessageBox.show({
					buttons: Ext.Msg.OK,
					fn: function() {
						current_window.findById('EUDDEW_EvnUslugaDispDop_setDate').focus(false);
					},
					icon: Ext.Msg.WARNING,
					msg: lang['davnost_etogo_issledovaniya_ne_mojet_byit_bolee_2h_let'],
					title: lang['oshibka']
				});
				return false;
			}
		}
		*/
		
		/* убрал, так как проверка вынесена в сохранение талона
		if ( pl_set_date > set_date && usluga_type_id != 6 && usluga_type_id != 5 )
		{
			if ( ( set_date.getMonthsBetween(pl_set_date) > 3 ) || ( set_date.getMonthsBetween(pl_set_date) == 3 && (set_date.getDate() != pl_set_date.getDate()) ) )
			{
				Ext.MessageBox.show({
					buttons: Ext.Msg.OK,
					fn: function() {
						current_window.findById('EUDDEW_EvnUslugaDispDop_setDate').focus(false);
					},
					icon: Ext.Msg.WARNING,
					msg: lang['davnost_etogo_issledovaniya_ne_mojet_byit_bolee_3h_mesyatsev'],
					title: lang['oshibka']
				});
				return false;
			}
		}*/
		
		if (record_status == 1)
		{
			record_status = 2;
		}

		index = current_window.findById('EUDDEW_DopDispUslugaTypeCombo').getStore().findBy(function(rec) { return rec.get('DopDispUslugaType_id') == dopdispuslugatype_id; });
		if (index >= 0)
		{
			dopdispuslugatype_name = current_window.findById('EUDDEW_DopDispUslugaTypeCombo').getStore().getAt(index).data.DopDispUslugaType_Name;
		}
		
		index = current_window.findById('EUDDEW_LpuSectionCombo').getStore().findBy(function(rec) { return rec.get('LpuSection_id') == lpu_section_id; });
		if (index >= 0)
		{
			lpu_section_name = current_window.findById('EUDDEW_LpuSectionCombo').getStore().getAt(index).data.LpuSection_Name;
		}

		var med_personal_fio = '';
		var med_personal_id = null;
		record = current_window.findById('EUDDEW_MedPersonalCombo').getStore().getById(med_staff_fact_id);
		if ( record ) {
			med_personal_fio = record.get('MedPersonal_Fio');
			med_personal_id = record.get('MedPersonal_id');
		}
		/*
		index = current_window.findById('EUDDEW_UslugaCombo').getStore().findBy(function(rec) { return rec.get('Usluga_id') == usluga_id; });
		if (index >= 0)
		{
			usluga_code = current_window.findById('EUDDEW_UslugaCombo').getStore().getAt(index).data.Usluga_Code;
			usluga_name = current_window.findById('EUDDEW_UslugaCombo').getStore().getAt(index).data.Usluga_Name;
		}
		*/
		if ( (usluga_id > 0) || ( getGlobalOptions().region && getGlobalOptions().region.nick == 'ufa' ) )
		{
			usluga_code = current_window.findById('EUDDEW_UslugaCombo').getFieldValue('Usluga_Code');
			usluga_name = current_window.findById('EUDDEW_UslugaCombo').getFieldValue('Usluga_Name');
		}
		else 
		{
			current_window.findById('EUDDEW_UslugaCombo').setValue(null);			
			sw.swMsg.alert(lang['oshibka'], lang['neobhodimo_vyibrat_sootvetsvuyuschuyu_uslugu'], function() { current_window.findById('EUDDEW_UslugaCombo').focus(); } );
			return false;
		}
		
		if (current_window.action != 'add')
		{
			add_flag = false;
		}
		var data = [{
			'EvnUslugaDispDop_id': current_window.findById('EUDDEW_EvnUslugaDispDop_id').getValue(),
			'EvnUslugaDispDop_setDate': current_window.findById('EUDDEW_EvnUslugaDispDop_setDate').getValue(),
			'EvnUslugaDispDop_didDate': current_window.findById('EUDDEW_EvnUslugaDispDop_didDate').getValue(),
			'DopDispUslugaType_id': dopdispuslugatype_id,
			'DopDispUslugaType_Name': dopdispuslugatype_name,
			'LpuSection_id': lpu_section_id,
			'ExaminationPlace_id': examination_place_id,
			'LpuSection_Name': lpu_section_name,
			'MedPersonal_id': med_personal_id,
			'MedPersonal_Fio': med_personal_fio,
			'Usluga_id': usluga_id,
			'Usluga_Code': usluga_code,
			'Usluga_Name': usluga_name,
			'Record_Status': record_status,
			'RateGrid_Data': this.ViewUslugaPokaz.getJSONChangedData(),
			'RateGrid_DataNumber': this.action == 'add' ? this.ViewUslugaPokaz.getNewDataSetNumber() : this.ViewUslugaPokaz.getSavedDataSetNumber()
		}];
		current_window.callback(data, add_flag);
		current_window.hide();
    },
	draggable: true,
    enableEdit: function(enable) {
    	if (enable)
    	{
			this.findById('EUDDEW_EvnUslugaDispDop_setDate').enable();
			this.findById('EUDDEW_EvnUslugaDispDop_didDate').enable();
			this.findById('EUDDEW_ExaminationPlaceCombo').enable();
			this.findById('EUDDEW_DopDispUslugaTypeCombo').enable();
			this.findById('EUDDEW_MedPersonalCombo').enable();
			this.findById('EUDDEW_LpuSectionCombo').enable();
			this.findById('EUDDEW_UslugaCombo').enable();
			// enable() для кнопок на гридах

			this.buttons[0].show();
		}
		else
    	{
			this.findById('EUDDEW_EvnUslugaDispDop_setDate').disable();
			this.findById('EUDDEW_EvnUslugaDispDop_didDate').disable();
			this.findById('EUDDEW_ExaminationPlaceCombo').disable();
			this.findById('EUDDEW_DopDispUslugaTypeCombo').disable();
			this.findById('EUDDEW_MedPersonalCombo').disable();
			this.findById('EUDDEW_LpuSectionCombo').disable();
			this.findById('EUDDEW_UslugaCombo').disable();

			// disable() для кнопок на гридах

			this.buttons[0].hide();
		}
    },
    height: 490,
	id: 'EvnUslugaDispDopEditWindow',
    initComponent: function() {
		this.ViewUslugaPokaz = new sw.Promed.RateGrid({
			title: lang['pokazateli'],
			id: 'EUDDEW_PropertyGrid',
			height: 200,
			border: true,
			region: 'south'
		});
	
        Ext.apply(this, {
            items: [
				new	sw.Promed.PersonInformationPanelShort({
					id: 'EUDDEW_PersonInformationFrame',
					region: 'north'
				}),
				new Ext.form.FormPanel({
					bodyBorder: false,
					bodyStyle: 'padding: 5px 5px 0',
					border: false,
					frame: false,
					id: 'EvnUslugaDispDopEditForm',
					labelAlign: 'right',
					labelWidth: 150,
					items: [{
						id: 'EUDDEW_EvnUslugaDispDop_id',
						name: 'EvnUslugaDispDop_id',
						value: 0,
						xtype: 'hidden'
					}, {
						id: 'EUDDEW_Record_Status',
						name: 'Record_Status',
						value: 0,
						xtype: 'hidden'
					}, {
						allowBlank: false,
						enableKeyEvents: true,
						fieldLabel: lang['data_issledovaniya'],
						format: 'd.m.Y',
						id: 'EUDDEW_EvnUslugaDispDop_setDate',
						listeners: {
							'keydown':  function(inp, e) {
								if ( e.shiftKey && e.getKey() == Ext.EventObject.TAB )
								{
									e.stopEvent();
									Ext.getCmp('EUDDEW_CancelButton').focus(true, 200);
								}
							},
							'change': function(field, newValue, oldValue) {	
								if (blockedDateAfterPersonDeath('personpanelid', 'EUDDEW_PersonInformationFrame', field, newValue, oldValue)) return;
								if ( newValue > 0 )
								{
									this.setDopDispUslugaTypeFilter();
									Ext.getCmp('EUDDEW_EvnUslugaDispDop_didDate').setValue(newValue);
									var base_form = this.findById('EvnUslugaDispDopEditForm').getForm();

									var lpu_section_id = base_form.findField('LpuSection_id').getValue();
									var med_staff_fact_id = base_form.findField('MedStaffFact_id').getValue();

									base_form.findField('LpuSection_id').clearValue();
									base_form.findField('MedStaffFact_id').clearValue();
																	
									var section_filter_params = {};									
									var medstafffact_filter_params = {};
									
									section_filter_params.onDate = Ext.util.Format.date(newValue, 'd.m.Y');
									medstafffact_filter_params.onDate = Ext.util.Format.date(newValue, 'd.m.Y');									
									//if (this.action == 'add' || this.action == 'edit')
									if (this.action == 'add')
										base_form.findField('Usluga_id').setFilterActualUsluga(newValue, null);
									
									// параклиника
									section_filter_params.arrayLpuUnitType = ['6'];
									medstafffact_filter_params.arrayLpuUnitType = ['6'];
									
									var user_med_staff_fact_id = this.UserMedStaffFact_id;
									var user_lpu_section_id = this.UserLpuSection_id;
									var user_med_staff_facts = this.UserMedStaffFacts;
									var user_lpu_sections = this.UserLpuSections;
									
									// фильтр или на конкретное место работы или на список мест работы
									if ( user_med_staff_fact_id && user_lpu_section_id && this.action == 'add' )
									{
										section_filter_params.id = user_lpu_section_id;
										medstafffact_filter_params.id = user_med_staff_fact_id;
									}
									else
										if ( user_med_staff_facts && user_lpu_sections && this.action == 'add' )
										{
											section_filter_params.ids = user_lpu_sections;
											medstafffact_filter_params.ids = user_med_staff_facts;
										}
									
									setLpuSectionGlobalStoreFilter(section_filter_params);
									setMedStaffFactGlobalStoreFilter(medstafffact_filter_params);

									base_form.findField('LpuSection_id').getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));
									base_form.findField('MedStaffFact_id').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));

									if ( base_form.findField('LpuSection_id').getStore().getById(lpu_section_id) ) {
										base_form.findField('LpuSection_id').setValue(lpu_section_id);
									}
								
									if ( base_form.findField('MedStaffFact_id').getStore().getById(med_staff_fact_id) ) {
										base_form.findField('MedStaffFact_id').setValue(med_staff_fact_id);
									}
									
									/*
										если форма отурыта на редактирование и задано отделение и 
										место работы или задан список мест работы, то не даем редактировать вообще
									*/
									if ( this.action == 'edit' && (( user_med_staff_fact_id && user_lpu_section_id ) || ( this.UserMedStaffFacts && this.UserMedStaffFacts.length > 0 )) )
									{
										base_form.findField('LpuSection_id').disable();
										base_form.findField('MedStaffFact_id').disable();
									}
									
									/*
										если форма отурыта на добавление и задано отделение и 
										место работы, то устанавливаем их не даем редактировать вообще
									*/
									if ( this.action == 'add' && user_med_staff_fact_id && user_lpu_section_id )
									{
										if ( base_form.findField('LpuSection_id').getStore().getById(user_lpu_section_id) ) {
											base_form.findField('LpuSection_id').setValue(user_lpu_section_id);
											base_form.findField('LpuSection_id').disable();
										}										
										if ( base_form.findField('MedStaffFact_id').getStore().getById(med_staff_fact_id) ) {
											base_form.findField('MedStaffFact_id').setValue(med_staff_fact_id);
											base_form.findField('MedStaffFact_id').disable();
										}
									}
									else
										/*
											если форма отурыта на добавление и задан список отделений и 
											мест работы, но он состоит из одного элемета,
											то устанавливаем значение и не даем редактировать
										*/
										if ( this.action == 'add' && this.UserMedStaffFacts && this.UserMedStaffFacts.length == 1 )
										{
											// список состоит из одного элемента (устанавливаем значение и не даем редактировать)
											if ( base_form.findField('LpuSection_id').getStore().getById(this.UserLpuSections[0]) ) {
												base_form.findField('LpuSection_id').setValue(this.UserLpuSections[0]);
												base_form.findField('LpuSection_id').disable();
											}										
											if ( base_form.findField('MedStaffFact_id').getStore().getById(this.UserMedStaffFacts[0]) ) {
												base_form.findField('MedStaffFact_id').setValue(this.UserMedStaffFacts[0]);
												base_form.findField('MedStaffFact_id').disable();
											}
										}
								}
							}.createDelegate(this)						
						},
						name: 'EvnUslugaDispDop_setDate',
						maxValue: Date.parseDate(getGlobalOptions().date, 'd.m.Y'),
						plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
						tabIndex: TABINDEX_EUDDEW+01,
						width: 100,
						xtype: 'swdatefield'
					}, {
						allowBlank: false,
						fieldLabel: lang['data_rezultata'],
						format: 'd.m.Y',
						id: 'EUDDEW_EvnUslugaDispDop_didDate',
						name: 'EvnUslugaDispDop_didDate',
						maxValue: Date.parseDate(getGlobalOptions().date, 'd.m.Y'),
						plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
						tabIndex: TABINDEX_EUDDEW+02,
						width: 100,
						xtype: 'swdatefield'
					}, {
						allowBlank: false,
						enableKeyEvents: true,
						fieldLabel: lang['vid'],
						id: 'EUDDEW_DopDispUslugaTypeCombo',
						listeners: {
							'change': function(field, newValue, oldValue) 
							{
								var fin = '';
								switch (newValue.toString())
								{
									
									case '3': fin = "Usluga_Code in ('02000101')"; break; // a. клинический анализ крови (02000101)
									case '13': fin = "Usluga_Code in ('02000401')"; break; // b. общий белок (02000401),
									case '1': fin = "Usluga_Code in ('02000456', '02000410')"; break; // c. холестерин (02000456 и 02000410)
									case '9': fin = "Usluga_Code in ('02003623')"; break;  // d. липопротеиды низкой плотности сыворотки крови (02003623)
									case '10': fin = "Usluga_Code in ('02003624')"; break; // e. триглицериды сыворотки крови (02003624)
									case '14': fin = "Usluga_Code in ('02000403')"; break; // f. креатинин (02000403),
									case '15': fin = "Usluga_Code in ('02000406')"; break; // g. мочевая кислота (02000406),
									case '16': fin = "Usluga_Code in ('02000435')"; break; // h. билирубин (02000435),
									case '17': fin = "Usluga_Code in ('02000423')"; break; // i. амилаза (02000423),
									case '2': fin = "Usluga_Code in ('02000071', '02000432')"; break; // j. сахар крови (02000071 и 02000432)
									case '4': fin = "Usluga_Code in ('02000130')"; break; // k. клинический анализ мочи (02000130)
									case '11': fin = "Usluga_Code in ('02000592')"; break; // l. онкомаркер специфический СА-125 (02000592) (только женщинам после 45 лет)
									case '12': fin = "Usluga_Code in ('02000593')"; break; // m. онкомаркер специфический PSI (02000593) (только мужчинам после 45 лет)
									case '7': fin = "Usluga_Code in ('02001101')"; break; // n. электрокардиография (02001101)
									case '6': fin = "Usluga_Code in ('02002301')"; break; // o. флюорография (02002301)
									case '5': fin = "Usluga_Code in ('02002230')"; break; // p. маммография (02002230) (только женщинам после 40 лет)
									case '18': fin = "Usluga_Code in ('02003316')"; break; // q. для женщин - цитологическое исследование мазка из цервикального канала (02003316).
									case '8': fin = "(1!=1)"; break; // q)	дополнительное обследование
									default: fin = "Usluga_Code in ('02000101', '02000401', '02000456', '02000410', '02003623', '02003624', '02000403','02000406','02000435',"+
									"'02000423','02000071', '02000432','02000130','02000592','02000593','02001101','02002301', '02002230', '02003316')"; break;
								}
								var combo = Ext.getCmp('EUDDEW_UslugaCombo');
								var usluga_id = combo.getValue();
								combo.setLoadQuery(newValue==8);
								//log(fin);
								combo.getStore().load(
								{
									callback: function() 
									{
										var fs = false;
										combo.getStore().each(function(record) 
										{
											if ((combo.getStore().getCount()==1) && (usluga_id==''))
											{
												usluga_id = record.get('Usluga_id');
												//log('1 '+usluga_id);
												combo.setValue(usluga_id);
											}
											//log(record.get('Usluga_id')+' = '+usluga_id);
											if (record.get('Usluga_id') == usluga_id)
											{
												combo.fireEvent('select', combo, record, 0);
												fs = true;
											}
										});
										if (!fs) 
										{
											combo.setValue('');
											combo.clearInvalid();
										}
									},
									params: { where: "where UslugaType_id = 2 and "+fin+" " }
								});
							}
						},
						name: 'DopDispUslugaType_id',
						tabIndex: TABINDEX_EUDDEW+03,
						validateOnBlur: false,
						width: 350,
						xtype: 'swdopdispuslugatypecombo'
					}, 
					{
						allowBlank: false,
						enableKeyEvents: true,
						id: 'EUDDEW_ExaminationPlaceCombo',
						listeners: 
						{
							'change': function(field, newValue, oldValue) 
							{
								// Проверка на выбранное место выполненения 
								// Если исследование проведено в «В своем МУ», в поле "Отделение" погружать все отделения своего МУ (как сейчас)
								// Если исследование проведено «В стационаре», поле Отделение делать пустым и неактивным
								// Если исследование проведено «В другом МУ», поле Отделение делать пустым и неактивным
								// http://172.19.61.14:81/issues/show/1594
								
								var fin = '';
								var LpuSectionCombo = Ext.getCmp('EUDDEW_LpuSectionCombo');
								var MedPersonalCombo = Ext.getCmp('EUDDEW_MedPersonalCombo');
								if (newValue.inlist([2,3]))
								{
									LpuSectionCombo.setDisabled(true);
									LpuSectionCombo.setValue(null);
									MedPersonalCombo.setDisabled(true);
									MedPersonalCombo.setValue(null);
								}
								else if ( this.action != 'view' )
								{
									LpuSectionCombo.setDisabled(false);
									MedPersonalCombo.setDisabled(false);
								}
							}.createDelegate(this)
						},
						name: 'ExaminationPlace_id',
						tabIndex: TABINDEX_EUDDEW+04,
						validateOnBlur: false,
						width: 350,
						xtype: 'swexaminationplacecombo'
					}, 
					{
						allowBlank: true,
						disabled: true,
						hiddenName: 'LpuSection_id',
						id: 'EUDDEW_LpuSectionCombo',
						lastQuery: '',
						listWidth: 650,
						linkedElements: [
							'EUDDEW_MedPersonalCombo'
						],
						tabIndex: TABINDEX_EUDDEW+05,
						width: 450,
						xtype: 'swlpusectionglobalcombo'
					}, {
						allowBlank: true,
						hiddenName: 'MedStaffFact_id',
						id: 'EUDDEW_MedPersonalCombo',
						lastQuery: '',
						listWidth: 650,
						parentElementId: 'EUDDEW_LpuSectionCombo',
						tabIndex: TABINDEX_EUDDEW+06,
						width: 450,
						xtype: 'swmedstafffactglobalcombo'
					}, {
						allowBlank: ( getGlobalOptions().region && getGlobalOptions().region.nick == 'ufa' ),
						id: 'EUDDEW_UslugaCombo',
						listWidth: 500,
						tabIndex: TABINDEX_EUDDEW+07,
						width: 450,
						listeners: {
							'beforequery': function(event) {
								var usluga_date_field = this.findById('EvnUslugaDispDopEditForm').getForm().findField('EvnUslugaDispDop_setDate');
								var usluga_date = usluga_date_field.getValue();
								if (!usluga_date)
								{
									sw.swMsg.alert(lang['oshibka'], lang['vyi_ne_ukazali_datu_vyipolneniya_uslugi'], function() { usluga_date_field.focus(); } );
									return false;
								}
								if (event.combo.Usluga_date != Ext.util.Format.date(usluga_date, 'd.m.Y'))
									event.combo.setFilterActualUsluga(usluga_date, null);
							}.createDelegate(this)
						},
						xtype: 'swuslugacombo'
					}
					],
					layout: 'form',
					reader: new Ext.data.JsonReader({
						success: function() { }
					}, [
						{ name: 'EvnUslugaDispDop_id' }
					]),
					region: 'center'
				}),
				this.ViewUslugaPokaz
			]
        });
    	sw.Promed.swEvnUslugaDispDopEditWindow.superclass.initComponent.apply(this, arguments);
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

        	var current_window = Ext.getCmp('EvnUslugaDispDopEditWindow');

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
    layout: 'border',
    listeners: {
    	'hide': function() {
    		this.onHide();
    	}
    },
    maximizable: true,
    minHeight: 370,
    minWidth: 700,
    modal: true,
    onHide: Ext.emptyFn,

	ownerWindow: null,
    plain: true,
    resizable: true,
	setDopDispUslugaTypeFilter: function() {
		var current_window = this;
		var set_date = this.findById('EUDDEW_EvnUslugaDispDop_setDate').getValue();
		var age_mam = this.age;
		if ( !set_date || set_date == '' ) 
			var age = this.age;
		else
		{			
			var birth_date = this.Person_Birthday;
			var age = (birth_date.getMonthsBetween(set_date) - (birth_date.getMonthsBetween(set_date) % 12)) / 12;			
		}
		
		var UsedDopDispUslugaType = this.UsedDopDispUslugaType;
		var sex_id = this.Sex_id;
		var dopdispuslugatype_combo = this.findById('EUDDEW_DopDispUslugaTypeCombo');
		dopdispuslugatype_combo.lastQuery='';
		dopdispuslugatype_combo.getStore().filterBy(function(record) {
			if ( record.data.DopDispUslugaType_id == 8 )
				return true;
			if ( 
				( record.data.DopDispUslugaType_id == 11 && (age < 45 || sex_id != 2) )
				|| ( record.data.DopDispUslugaType_id == 12 && (age < 45 || sex_id != 1) )
				|| ( record.data.DopDispUslugaType_id == 5 && (age_mam < 40 || sex_id != 2 || current_window.EvnPLDispDop_IsNotMammograf == true))
				|| ( record.data.DopDispUslugaType_id == 18 && (sex_id != 2 || current_window.EvnPLDispDop_IsNotCito == true ) )
			)
				return false;
			return UsedDopDispUslugaType[record.data.DopDispUslugaType_id]!=1;
		});
	},
    show: function() {
		sw.Promed.swEvnUslugaDispDopEditWindow.superclass.show.apply(this, arguments);

		var current_window = this;

		current_window.restore();
		current_window.center();

        var form = current_window.findById('EvnUslugaDispDopEditForm');
		var base_form = form.getForm();
		form.getForm().reset();

       	current_window.callback = Ext.emptyFn;
       	current_window.onHide = Ext.emptyFn;
		current_window.ownerWindow = null;

        if (!arguments[0] || !arguments[0].formParams || !arguments[0].ownerWindow)
        {
        	Ext.Msg.alert(lang['soobschenie'], lang['nevernyie_parametryi'], function() { current_window.hide(); } );
        	return false;
        }

		/*var lpu_section_combo = this.findById('EUDDEW_LpuSectionCombo');
		if (lpu_section_combo.getStore().getCount() == 0)
		{
			lpu_section_combo.getStore().load({
				callback: function(records, options, success) {
					if (!success)
					{
						Ext.Msg.alert(lang['oshibka'], lang['oshibka_pri_zagruzke_spravochnika_otdeleniy_poseschenie']);
						return false;
					}

					lpu_section_combo.setValue(lpu_section_combo.getValue());
					lpu_section_combo.clearInvalid();
				}
			});
		}

		var med_personal_combo = this.findById('EUDDEW_MedPersonalCombo');
		if (med_personal_combo.getStore().getCount() == 0)
		{
			med_personal_combo.getStore().load({
				callback: function(records, options, success) {
					if (!success)
					{
						Ext.Msg.alert(lang['oshibka'], lang['oshibka_pri_zagruzke_med_personala_poseschenie']);
						return false;
					}

					med_personal_combo.setValue(med_personal_combo.getValue());
					med_personal_combo.clearInvalid();
				}
			});
		}*/

        if (arguments[0].action)
        {
        	current_window.action = arguments[0].action;
        }
		
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
		
		this.EvnPLDispDop_IsNotMammograf = false;
		if (arguments[0].EvnPLDispDop_IsNotMammograf)
		{
			this.EvnPLDispDop_IsNotMammograf = arguments[0].EvnPLDispDop_IsNotMammograf;
		}
		
		this.EvnPLDispDop_IsNotCito = false;
		if (arguments[0].EvnPLDispDop_IsNotCito)
		{
			this.EvnPLDispDop_IsNotCito = arguments[0].EvnPLDispDop_IsNotCito;
		}
		
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

		current_window.findById('EUDDEW_PersonInformationFrame').load({
			Person_id: (arguments[0].Person_id ? arguments[0].Person_id : ''),
			Person_Birthday: (arguments[0].Person_Birthday ? arguments[0].Person_Birthday : ''),
			Person_Firname: (arguments[0].Person_Firname ? arguments[0].Person_Firname : ''),
			Person_Secname: (arguments[0].Person_Secname ? arguments[0].Person_Secname : ''),
			Person_Surname: (arguments[0].Person_Surname ? arguments[0].Person_Surname : ''),
			callback: function() {
				var field = base_form.findField('EvnUslugaDispDop_setDate');
				clearDateAfterPersonDeath('personpanelid', 'EUDDEW_PersonInformationFrame', field);
			}
		});

  		var loadMask = new Ext.LoadMask(Ext.get('EvnUslugaDispDopEditWindow'), { msg: LOAD_WAIT });
		loadMask.show();
		
		// чистим фильтр
		this.findById('EUDDEW_DopDispUslugaTypeCombo').getStore().clearFilter();

        form.getForm().setValues(arguments[0].formParams);
        form.getForm().clearInvalid();

		var sex_id = arguments[0].Sex_id;
		var age = arguments[0].Person_Age;
		
		var usluga_combo = current_window.findById('EUDDEW_UslugaCombo');
		
		var med_personal_id = arguments[0].formParams.MedPersonal_id;
		
		this.UsedDopDispUslugaType = arguments[0].formParams['UsedDopDispUslugaType'];
		this.age = arguments[0].Person_Age;
		this.Sex_id = arguments[0].Sex_id;
		this.Person_Birthday = arguments[0].Person_Birthday;
		
		//загрузка таблицы(grid) с показателями		
		var dataset_num = arguments[0].formParams.RateGrid_DataNumber && arguments[0].formParams.RateGrid_DataNumber != "" ? arguments[0].formParams.RateGrid_DataNumber : 0; //проверяем есть ли номер датасета для данной услуги		
		this.ViewUslugaPokaz.clear();
		if(dataset_num > 0){
			this.ViewUslugaPokaz.restoreGridCopy(dataset_num);
		} else {
			if (arguments[0].formParams.EvnUslugaDispDop_id && current_window.action != 'add')
				this.ViewUslugaPokaz.loadData({rate_type: 'evnusluga', rate_subid: arguments[0].formParams.EvnUslugaDispDop_id}); //загрузка показателей для услуги
		}
		
		
		switch (current_window.action)
		{
			case 'add':
				current_window.setTitle(lang['laboratornoe_issledovanie_dobavlenie']);
				current_window.enableEdit(true);

				// Фильтруем виды исследований, показываем только незанятые
				var UsedDopDispUslugaType = arguments[0].formParams['UsedDopDispUslugaType'];
				var dopdispuslugatype_combo = this.findById('EUDDEW_DopDispUslugaTypeCombo');
				dopdispuslugatype_combo.lastQuery='';
				dopdispuslugatype_combo.getStore().filterBy(function(record) {
					if ( record.data.DopDispUslugaType_id == 8 )
						return true;
					if ( ( record.data.DopDispUslugaType_id == 11 && (age < 45 || sex_id != 2) ) || ( record.data.DopDispUslugaType_id == 12 && (age < 45 || sex_id != 1) ) || 
					( record.data.DopDispUslugaType_id == 5 && (age < 40 || sex_id != 2) ) || 
					( record.data.DopDispUslugaType_id == 18 && sex_id != 2 )  )
						return false;					
					return UsedDopDispUslugaType[record.data.DopDispUslugaType_id] != 1;
				});				
				
				loadMask.hide();
				current_window.findById('EUDDEW_EvnUslugaDispDop_setDate').focus(false, 250);

                break;

        	case 'edit':
        	    current_window.setTitle(lang['laboratornoe_issledovanie_redaktirovanie']);
                current_window.enableEdit(true);
				current_window.findById('EUDDEW_ExaminationPlaceCombo').fireEvent('change', current_window.findById('EUDDEW_ExaminationPlaceCombo'), current_window.findById('EUDDEW_ExaminationPlaceCombo').getValue());
				// Фильтруем виды исследований, показываем только незанятые
				var UsedDopDispUslugaType = arguments[0].formParams['UsedDopDispUslugaType'];
				var dopdispuslugatype_combo = this.findById('EUDDEW_DopDispUslugaTypeCombo');
				dopdispuslugatype_combo.lastQuery='';
				dopdispuslugatype_combo.getStore().filterBy(function(record) {
					if ( record.data.DopDispUslugaType_id == 8 )
						return true;
					if ( ( record.data.DopDispUslugaType_id == 11 && (age < 45 || sex_id != 2) ) || ( record.data.DopDispUslugaType_id == 12 && (age < 45 || sex_id != 1) ) || ( record.data.DopDispUslugaType_id == 5 && (age < 40 || sex_id != 2) ) || ( record.data.DopDispUslugaType_id == 18 && sex_id != 2 )  )
						return false;
					return UsedDopDispUslugaType[record.data.DopDispUslugaType_id]!=1;
				});
				
				var usluga_id = usluga_combo.getValue();
				if (usluga_id != null && usluga_id.toString().length > 0)
				{
					usluga_combo.getStore().load({
						callback: function() {
							usluga_combo.getStore().each(function(record) {
								if (record.data.Usluga_id == usluga_id)
								{
									usluga_combo.setValue(usluga_id);
									usluga_combo.fireEvent('select', usluga_combo, record, 0);
								}
							});
						},
						params: { where: "where UslugaType_id = 2 and Usluga_id = " + usluga_id }
					});
				}
				
				// устанавливаем врача
				current_window.findById('EUDDEW_MedPersonalCombo').getStore().findBy(function(record) {
					if ( record.get('MedPersonal_id') == med_personal_id )
					{
						current_window.findById('EUDDEW_MedPersonalCombo').setValue(record.get('MedStaffFact_id'));
						return true;
					}
				});
				loadMask.hide();
				current_window.findById('EUDDEW_EvnUslugaDispDop_setDate').fireEvent('change', current_window.findById('EUDDEW_EvnUslugaDispDop_setDate'), current_window.findById('EUDDEW_EvnUslugaDispDop_setDate').getValue());
				current_window.findById('EUDDEW_EvnUslugaDispDop_setDate').focus(false, 250);
                break;

            case 'view':
                current_window.setTitle(lang['laboratornoe_issledovanie_prosmotr']);
                current_window.enableEdit(false);
				current_window.findById('EUDDEW_ExaminationPlaceCombo').fireEvent('change', current_window.findById('EUDDEW_ExaminationPlaceCombo'), current_window.findById('EUDDEW_ExaminationPlaceCombo').getValue());
				var usluga_id = usluga_combo.getValue();
				if (usluga_id != null && usluga_id.toString().length > 0)
				{
					usluga_combo.getStore().load({
						callback: function() {
							usluga_combo.getStore().each(function(record) {
								if (record.data.Usluga_id == usluga_id)
								{
									usluga_combo.setValue(usluga_id);
									usluga_combo.fireEvent('select', usluga_combo, record, 0);
								}
							});
						},
						params: { where: "where UslugaType_id = 2 and Usluga_id = " + usluga_id }
					});
				}
				// устанавливаем врача
				current_window.findById('EUDDEW_MedPersonalCombo').getStore().findBy(function(record) {
					if ( record.get('MedPersonal_id') == med_personal_id )
					{
						current_window.findById('EUDDEW_MedPersonalCombo').setValue(record.get('MedStaffFact_id'));
						return true;
					}
				});
				current_window.findById('EUDDEW_EvnUslugaDispDop_setDate').fireEvent('change', current_window.findById('EUDDEW_EvnUslugaDispDop_setDate'), current_window.findById('EUDDEW_EvnUslugaDispDop_setDate').getValue());
				loadMask.hide();
				current_window.buttons[1].focus();

                break;
        }

        form.getForm().clearInvalid();
    },
    width: 700
});