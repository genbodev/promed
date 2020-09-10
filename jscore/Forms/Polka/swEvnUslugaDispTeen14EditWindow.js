/**
* swEvnUslugaDispTeen14EditWindow - окно редактирования/добавления выполнения лабораторного исследования по диспансеризации подростков 14 лет
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package         Polka
* @access          public
* @copyright       Copyright (c) 2009 - 2011 Swan Ltd.
* @author          Ivan Pshenitcyn aka IVP (ipshon@gmail.com)
* @version         01.08.2011
* @comment         Префикс для id компонентов EUDT14EW (swEvnUslugaDispTeen14EditWindow)
*                  tabIndex: TABINDEX_EUDT14EW (2800)
*
*
* Использует: окно редактирования талона по доп. диспансеризации (swEvnPLDispTeen14EditWindow)
*/

sw.Promed.swEvnUslugaDispTeen14EditWindow = Ext.extend(sw.Promed.BaseForm, {
	action: null,
	buttonAlign: 'left',
	buttons: [{
		handler: function() {
			this.ownerCt.doSave();
		},
		iconCls: 'save16',
		id: 'EUDT14EW_SaveButton',
		tabIndex: TABINDEX_EUDT14EW+05,
		text: BTN_FRMSAVE
	}, {
		handler: function() {
			this.ownerCt.hide();
		},
		iconCls: 'cancel16',
		id: 'EUDT14EW_CancelButton',
		onTabAction: function() {
			this.findById('EUDT14EW_EvnUslugaDispTeen14_setDate').focus(true, 200);
		}.createDelegate(this),
		onShiftTabAction: function() {
			this.findById('EUDT14EW_SaveButton').focus(true, 200);
		}.createDelegate(this),
		tabIndex: TABINDEX_EUDT14EW+05,
		text: BTN_FRMCANCEL
	}, 
		HelpButton(this, TABINDEX_EUDT14EW+10)
	],
    callback: Ext.emptyFn,
    closable: true,
    closeAction: 'hide',
    collapsible: true,
	doSave: function() {
		var add_flag = true;
		var current_window = this;
		var index = -1;
		var DispUslugaTeen14Type_id = current_window.findById('EUDT14EW_DispUslugaTeen14TypeCombo').getValue();
		var DispUslugaTeen14Type_name = '';
		var lpu_section_id = current_window.findById('EUDT14EW_LpuSectionCombo').getValue();
		var lpu_section_name = '';
		var med_staff_fact_id = current_window.findById('EUDT14EW_MedPersonalCombo').getValue();
		var med_personal_fio = '';
		var usluga_id = current_window.findById('EUDT14EW_UslugaCombo').getValue();
		var studytype_id = current_window.findById('EUDT14EW_StudyTypeCombo').getValue();
		var studytype_name = current_window.findById('EUDT14EW_StudyTypeCombo').getRawValue();
		var usluga_code = '';
		var usluga_name = '';
		var examination_place_id = current_window.findById('EUDT14EW_ExaminationPlaceCombo').getValue();
		var record_status = current_window.findById('EUDT14EW_Record_Status').getValue();

        //Проверка на наличие у врача кода ДЛО или специальности https://redmine.swan.perm.ru/issues/47172
        if(getRegionNick().inlist([ 'kareliya' ])){
            var MedSpecOms_id = '';
            var MedPersonal_DloCode = '';
            current_window.findById('EUDT14EW_MedPersonalCombo').getStore().findBy(function(record) {
                if ( record.get('MedStaffFact_id') == med_staff_fact_id )
                {
                    MedSpecOms_id = (!Ext.isEmpty(record.get('MedSpecOms_id'))) ? record.get('MedSpecOms_id') : '';
                    MedPersonal_DloCode = (!Ext.isEmpty(record.get('MedPersonal_DloCode'))) ? record.get('MedPersonal_DloCode') : '';
                }
            });
            if(MedSpecOms_id == ''){
                Ext.Msg.alert(lang['soobschenie'], lang['u_vracha_ne_ukazana_spetsialnost'], function() {  current_window.findById('EUDT14EW_MedPersonalCombo').clearValue(); } );
                return false;
            }
            if(MedPersonal_DloCode == ''){
                Ext.Msg.alert(lang['soobschenie'], lang['u_vracha_ne_ukazan_kod_dlo'], function() {  current_window.findById('EUDT14EW_MedPersonalCombo').clearValue(); } );
                return false;
            }
        }
		if (!current_window.findById('EvnUslugaDispTeen14EditForm').getForm().isValid())
		{
            Ext.MessageBox.show({
                buttons: Ext.Msg.OK,
                fn: function() {
                	current_window.findById('EUDT14EW_EvnUslugaDispTeen14_setDate').focus(false);
                },
                icon: Ext.Msg.WARNING,
                msg: ERR_INVFIELDS_MSG,
                title: ERR_INVFIELDS_TIT
            });
            return false;
		}
		
		if ( current_window.findById('EUDT14EW_EvnUslugaDispTeen14_setDate').getValue() > current_window.findById('EUDT14EW_EvnUslugaDispTeen14_didDate').getValue() )
		{
            Ext.MessageBox.show({
                buttons: Ext.Msg.OK,
                fn: function() {
                	current_window.findById('EUDT14EW_EvnUslugaDispTeen14_setDate').focus(false);
                },
                icon: Ext.Msg.WARNING,
                msg: lang['data_issledovaniya_ne_mojet_prevyishat_datu_polucheniya_rezultata'],
                title: lang['oshibka']
            });
            return false;
		}
		
		var set_date = current_window.findById('EUDT14EW_EvnUslugaDispTeen14_setDate').getValue();
		var did_date = current_window.findById('EUDT14EW_EvnUslugaDispTeen14_didDate').getValue();		
		if ( ( set_date.getMonthsBetween(did_date) > 3 ) || ( set_date.getMonthsBetween(did_date) == 3 && (set_date.getDate() != did_date.getDate()) ) )
		{
			Ext.MessageBox.show({
                buttons: Ext.Msg.OK,
                fn: function() {
                	current_window.findById('EUDT14EW_EvnUslugaDispTeen14_setDate').focus(false);
                },
                icon: Ext.Msg.WARNING,
                msg: lang['data_polucheniya_rezultata_laboratornogo_issledovaniya_ne_bolee_3-h_mesyatsev_s_datyi_issledovaniya'],
                title: lang['oshibka']
            });
            return false;
		}
		
		/* убрал проверку, так как надо проверять при сохранении талона
		var pl_set_date = current_window.set_date;
		var set_date = current_window.findById('EUDT14EW_EvnUslugaDispTeen14_setDate').getValue();
		var usluga_type_id = Ext.getCmp('EUDT14EW_DispUslugaTeen14TypeCombo').getValue();
		if ( pl_set_date > set_date && (usluga_type_id == 6 || usluga_type_id == 5) )
		{
			if ( ( set_date.getMonthsBetween(pl_set_date) > 24 ) || ( set_date.getMonthsBetween(pl_set_date) == 24 && (set_date.getDate() != pl_set_date.getDate()) ) )
			{
				Ext.MessageBox.show({
					buttons: Ext.Msg.OK,
					fn: function() {
						current_window.findById('EUDT14EW_EvnUslugaDispTeen14_setDate').focus(false);
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
						current_window.findById('EUDT14EW_EvnUslugaDispTeen14_setDate').focus(false);
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

		index = current_window.findById('EUDT14EW_DispUslugaTeen14TypeCombo').getStore().findBy(function(rec) { return rec.get('DispUslugaTeen14Type_id') == DispUslugaTeen14Type_id; });
		if (index >= 0)
		{
			DispUslugaTeen14Type_name = current_window.findById('EUDT14EW_DispUslugaTeen14TypeCombo').getStore().getAt(index).data.DispUslugaTeen14Type_Name;
		}
		
		index = current_window.findById('EUDT14EW_LpuSectionCombo').getStore().findBy(function(rec) { return rec.get('LpuSection_id') == lpu_section_id; });
		if (index >= 0)
		{
			lpu_section_name = current_window.findById('EUDT14EW_LpuSectionCombo').getStore().getAt(index).data.LpuSection_Name;
		}

		var med_personal_fio = '';
		var med_personal_id = null;
		record = current_window.findById('EUDT14EW_MedPersonalCombo').getStore().getById(med_staff_fact_id);
		if ( record ) {
			med_personal_fio = record.get('MedPersonal_Fio');
			med_personal_id = record.get('MedPersonal_id');
		}
		/*
		index = current_window.findById('EUDT14EW_UslugaCombo').getStore().findBy(function(rec) { return rec.get('Usluga_id') == usluga_id; });
		if (index >= 0)
		{
			usluga_code = current_window.findById('EUDT14EW_UslugaCombo').getStore().getAt(index).data.Usluga_Code;
			usluga_name = current_window.findById('EUDT14EW_UslugaCombo').getStore().getAt(index).data.Usluga_Name;
		}
		*/
		if ( (usluga_id > 0) || ( getGlobalOptions().region && getGlobalOptions().region.nick == 'ufa' ) )
		{
			usluga_code = current_window.findById('EUDT14EW_UslugaCombo').getFieldValue('Usluga_Code');
			usluga_name = current_window.findById('EUDT14EW_UslugaCombo').getFieldValue('Usluga_Name');
		}
		else 
		{
			current_window.findById('EUDT14EW_UslugaCombo').setValue(null);			
			sw.swMsg.alert(lang['oshibka'], lang['neobhodimo_vyibrat_sootvetsvuyuschuyu_uslugu'], function() { current_window.findById('EUDT14EW_UslugaCombo').focus(); } );
			return false;
		}
		
		if (current_window.action != 'add')
		{
			add_flag = false;
		}
		var data = [{
			'EvnUslugaDispTeen14_id': current_window.findById('EUDT14EW_EvnUslugaDispTeen14_id').getValue(),
			'EvnUslugaDispTeen14_setDate': current_window.findById('EUDT14EW_EvnUslugaDispTeen14_setDate').getValue(),
			'EvnUslugaDispTeen14_didDate': current_window.findById('EUDT14EW_EvnUslugaDispTeen14_didDate').getValue(),
			'DispUslugaTeen14Type_id': DispUslugaTeen14Type_id,
			'DispUslugaTeen14Type_Name': DispUslugaTeen14Type_name,
			'LpuSection_id': lpu_section_id,
			'ExaminationPlace_id': examination_place_id,
			'LpuSection_Name': lpu_section_name,
			'MedPersonal_id': med_personal_id,
			'MedStaffFact_id': med_staff_fact_id,
			'MedPersonal_Fio': med_personal_fio,
			'Usluga_id': usluga_id,
			'StudyType_id': studytype_id,
			'StudyType_Name': studytype_name,
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
			this.findById('EUDT14EW_EvnUslugaDispTeen14_setDate').enable();
			this.findById('EUDT14EW_EvnUslugaDispTeen14_didDate').enable();
			this.findById('EUDT14EW_ExaminationPlaceCombo').enable();
			this.findById('EUDT14EW_DispUslugaTeen14TypeCombo').enable();
			this.findById('EUDT14EW_MedPersonalCombo').enable();
			this.findById('EUDT14EW_LpuSectionCombo').enable();
			this.findById('EUDT14EW_StudyTypeCombo').enable();
			this.findById('EUDT14EW_UslugaCombo').enable();
			// enable() для кнопок на гридах

			this.buttons[0].show();
		}
		else
    	{
			this.findById('EUDT14EW_EvnUslugaDispTeen14_setDate').disable();
			this.findById('EUDT14EW_EvnUslugaDispTeen14_didDate').disable();
			this.findById('EUDT14EW_ExaminationPlaceCombo').disable();
			this.findById('EUDT14EW_DispUslugaTeen14TypeCombo').disable();
			this.findById('EUDT14EW_MedPersonalCombo').disable();
			this.findById('EUDT14EW_LpuSectionCombo').disable();
			this.findById('EUDT14EW_StudyTypeCombo').disable();
			this.findById('EUDT14EW_UslugaCombo').disable();

			// disable() для кнопок на гридах

			this.buttons[0].hide();
		}
    },
    height: 290,
	id: 'EvnUslugaDispTeen14EditWindow',
    initComponent: function() {
        var win = this;
		this.ViewUslugaPokaz = new sw.Promed.RateGrid({
			title: lang['pokazateli'],
			id: 'EUDT14EW_PropertyGrid',
			height: 0,
			hidden: true,
			border: true,
			region: 'south'
		});
	
        Ext.apply(this, {
            items: [
				new	sw.Promed.PersonInformationPanelShort({
					id: 'EUDT14EW_PersonInformationFrame',
					region: 'north'
				}),
				new Ext.form.FormPanel({
					bodyBorder: false,
					bodyStyle: 'padding: 5px 5px 0',
					border: false,
					frame: false,
					id: 'EvnUslugaDispTeen14EditForm',
					labelAlign: 'right',
					labelWidth: 150,
					items: [{
						id: 'EUDT14EW_EvnUslugaDispTeen14_id',
						name: 'EvnUslugaDispTeen14_id',
						value: 0,
						xtype: 'hidden'
					}, {
						id: 'EUDT14EW_Record_Status',
						name: 'Record_Status',
						value: 0,
						xtype: 'hidden'
					}, {
						allowBlank: false,
						enableKeyEvents: true,
						fieldLabel: lang['data_issledovaniya'],
						format: 'd.m.Y',
						id: 'EUDT14EW_EvnUslugaDispTeen14_setDate',
						listeners: {
							'keydown':  function(inp, e) {
								if ( e.shiftKey && e.getKey() == Ext.EventObject.TAB )
								{
									e.stopEvent();
									Ext.getCmp('EUDT14EW_CancelButton').focus(true, 200);
								}
							},
							'change': function(field, newValue, oldValue) {	
								if (blockedDateAfterPersonDeath('personpanelid', 'EUDT14EW_PersonInformationFrame', field, newValue, oldValue)) return;
								if ( newValue > 0 )
								{
									//this.setUslugaFilter();
									Ext.getCmp('EUDT14EW_EvnUslugaDispTeen14_didDate').setValue(newValue);
									var base_form = this.findById('EvnUslugaDispTeen14EditForm').getForm();

									var lpu_section_id = base_form.findField('LpuSection_id').getValue();
									if ( this.first_msf )
										var med_staff_fact_id = this.first_msf;
									else
										var med_staff_fact_id = base_form.findField('MedStaffFact_id').getValue();

									base_form.findField('LpuSection_id').clearValue();
									base_form.findField('MedStaffFact_id').clearValue();
																	
									var section_filter_params = {};									
									var medstafffact_filter_params = {};
									
									section_filter_params.onDate = Ext.util.Format.date(newValue, 'd.m.Y');
									medstafffact_filter_params.onDate = Ext.util.Format.date(newValue, 'd.m.Y');									
									//if (this.action == 'add' || this.action == 'edit')
									//if (this.action == 'add')
										//base_form.findField('Usluga_id').setFilterActualUsluga(newValue, null);
									
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
						name: 'EvnUslugaDispTeen14_setDate',
						maxValue: Date.parseDate(getGlobalOptions().date, 'd.m.Y'),
						plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
						tabIndex: TABINDEX_EUDT14EW+01,
						width: 100,
						xtype: 'swdatefield'
					}, {
						allowBlank: false,
						fieldLabel: lang['data_rezultata'],
						format: 'd.m.Y',
						id: 'EUDT14EW_EvnUslugaDispTeen14_didDate',
						name: 'EvnUslugaDispTeen14_didDate',
						maxValue: Date.parseDate(getGlobalOptions().date, 'd.m.Y'),
						plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
						tabIndex: TABINDEX_EUDT14EW+02,
						width: 100,
						xtype: 'swdatefield'
					}, 
					{
						allowBlank: false,
						enableKeyEvents: true,
						id: 'EUDT14EW_ExaminationPlaceCombo',
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
								var LpuSectionCombo = Ext.getCmp('EUDT14EW_LpuSectionCombo');
								var MedPersonalCombo = Ext.getCmp('EUDT14EW_MedPersonalCombo');
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
						tabIndex: TABINDEX_EUDT14EW+04,
						validateOnBlur: false,
						width: 350,
						xtype: 'swexaminationplacecombo'
					}, 
					{
						allowBlank: true,
						disabled: true,
						hiddenName: 'LpuSection_id',
						id: 'EUDT14EW_LpuSectionCombo',
						lastQuery: '',
						listWidth: 650,
						linkedElements: [
							'EUDT14EW_MedPersonalCombo'
						],
						tabIndex: TABINDEX_EUDT14EW+05,
						width: 450,
						xtype: 'swlpusectionglobalcombo'
					}, {
						allowBlank: true,
						hiddenName: 'MedStaffFact_id',
						id: 'EUDT14EW_MedPersonalCombo',
						lastQuery: '',
						listWidth: 650,
						parentElementId: 'EUDT14EW_LpuSectionCombo',
                            listeners: {
                                'change': function(field, newValue, oldValue) {
                                    if(getRegionNick().inlist([ 'kareliya' ])){
                                        var MedSpecOms_id = '';
                                        var MedPersonal_DloCode = '';
										var MedPersonal_Snils = '';
                                        win.findById('EUDT14EW_MedPersonalCombo').getStore().findBy(function(record) {
                                            if ( record.get('MedStaffFact_id') == newValue )
                                            {
                                                MedSpecOms_id = (!Ext.isEmpty(record.get('MedSpecOms_id'))) ? record.get('MedSpecOms_id') : '';
                                                MedPersonal_DloCode = (!Ext.isEmpty(record.get('MedPersonal_DloCode'))) ? record.get('MedPersonal_DloCode') : '';
												MedPersonal_Snils = (!Ext.isEmpty(record.get('Person_Snils'))) ? record.get('Person_Snils') : '';
                                                return true;
                                            }
                                        });
                                        if(newValue > 0)
                                        {
                                            if(MedSpecOms_id == ''){
                                                Ext.Msg.alert(lang['soobschenie'], lang['u_vracha_ne_ukazana_spetsialnost'], function() {  win.findById('EUDT14EW_MedPersonalCombo').clearValue(); } );
                                            }
											if(getRegionNick() == 'penza'){
												if(MedPersonal_DloCode == ''){
													Ext.Msg.alert(lang['soobschenie'], lang['u_vracha_ne_ukazan_kod_dlo'], function() {  win.findById('EUDD13EW_MedPersonalCombo').clearValue(); } );
												}
											}
											else {
												if(MedPersonal_Snils == ''){
													Ext.Msg.alert(lang['soobschenie'], lang['u_vracha_ne_ukazan_snils'], function() {  win.findById('EUDD13EW_MedPersonalCombo').clearValue(); } );
												}
											}
                                        }
                                    }
                                }
                            },
						tabIndex: TABINDEX_EUDT14EW+05,
						width: 450,
						xtype: 'swmedstafffactglobalcombo'
					}, {
						allowBlank: false,
						comboSubject: 'StudyType',
						id: 'EUDT14EW_StudyTypeCombo',
						disabled: false,
						fieldLabel: lang['vid_issledovaniya'],
						tabIndex: TABINDEX_EUDT14EW+05,
						width: 450,
						xtype: 'swcommonsprcombo',
						listeners: {
							'change': function(combo, newValue) {
								this.setUslugaFilter();
							}.createDelegate(this)
						}
					}, {
						allowBlank: true,
						enableKeyEvents: true,
						hidden: true,
						hideLabel: true,
						fieldLabel: lang['vid'],
						id: 'EUDT14EW_DispUslugaTeen14TypeCombo',
						listeners: {
							'change': function(field, newValue, oldValue)
							{

							}
						},
						name: 'DispUslugaTeen14Type_id',
						tabIndex: TABINDEX_EUDT14EW+05,
						validateOnBlur: false,
						width: 350,
						xtype: 'swdispuslugateen14typecombo'
					}, {
						allowBlank: ( getGlobalOptions().region && getGlobalOptions().region.nick == 'ufa' ),
						id: 'EUDT14EW_UslugaCombo',
						listWidth: 500,
						tabIndex: TABINDEX_EUDT14EW+05,
						width: 450,
						listeners: {
							'beforequery': function(event) {
								var usluga_date_field = this.findById('EvnUslugaDispTeen14EditForm').getForm().findField('EvnUslugaDispTeen14_setDate');
								var usluga_date = usluga_date_field.getValue();
								if (!usluga_date)
								{
									sw.swMsg.alert(lang['oshibka'], lang['vyi_ne_ukazali_datu_vyipolneniya_uslugi'], function() { usluga_date_field.focus(); } );
									return false;
								}
								/*if (event.combo.Usluga_date != Ext.util.Format.date(usluga_date, 'd.m.Y'))
									event.combo.setFilterActualUsluga(usluga_date, null);*/
							}.createDelegate(this)
						},
						xtype: 'swuslugacombo'
					}
					],
					layout: 'form',
					reader: new Ext.data.JsonReader({
						success: function() { }
					}, [
						{ name: 'EvnUslugaDispTeen14_id' }
					]),
					region: 'center'
				}),
				this.ViewUslugaPokaz
			]
        });
    	sw.Promed.swEvnUslugaDispTeen14EditWindow.superclass.initComponent.apply(this, arguments);
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

        	var current_window = Ext.getCmp('EvnUslugaDispTeen14EditWindow');

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
	setDispUslugaTeen14TypeFilter: function() {
		var current_window = this;
		var set_date = this.findById('EUDT14EW_EvnUslugaDispTeen14_setDate').getValue();
		if ( !set_date || set_date == '' )
			var age = this.age;
		else
		{			
			var birth_date = this.Person_Birthday;
			var age = (birth_date.getMonthsBetween(set_date) - (birth_date.getMonthsBetween(set_date) % 12)) / 12;			
		}
		var UsedDispUslugaTeen14Type = this.UsedDispUslugaTeen14Type;
		var UsedDispUslugaTeen14Type_Code = this.UsedDispUslugaTeen14Type_Code;
		var sex_id = this.Sex_id;
		var DispUslugaTeen14Type_combo = this.findById('EUDT14EW_DispUslugaTeen14TypeCombo');
		DispUslugaTeen14Type_combo.lastQuery='';
		DispUslugaTeen14Type_combo.getStore().filterBy(function(record) {
			if ( 
				( record.data.DispUslugaTeen14Type_id == 5 &&  sex_id == 1 )
				|| ( record.data.DispUslugaTeen14Type_id == 8 &&  sex_id == 2 )
			)
				return false;
			return UsedDispUslugaTeen14Type[record.data.DispUslugaTeen14Type_id] != 1;
		});
	},
	setUslugaFilter: function() {
		// фильтруем услуги
		// если дополнительный тип исследования, то делаем доступным только выбор услуги из справочника
		var combo = Ext.getCmp('EUDT14EW_UslugaCombo');


		var usluga_id = combo.getValue();

		if ( Ext.getCmp('EUDT14EW_StudyTypeCombo').getValue() == 2 )
		{
			combo.getStore().removeAll();
			combo.clearValue();
			combo.lastQuery = '';
			combo.setLoadQuery(true);
			if ( usluga_id > 0 )
			{
				combo.getStore().load(
				{
					callback: function() 
					{
						var fs = false;
						combo.getStore().each(function(record) 
						{
							//if ((combo.getStore().getCount()==1) && (usluga_id==''))
							//{
								//usluga_id = record.get('Usluga_id');
								//log('1 '+usluga_id);								
							//}
							//log(record.get('Usluga_id')+' = '+usluga_id);
							if (record.get('Usluga_id') == usluga_id)
							{
								combo.setValue(usluga_id);
								combo.fireEvent('select', combo, record, 0);
								fs = true;
							}
						});
						if (!fs) 
						{
							combo.clearValue();
							combo.clearInvalid();
						}
					},
					params: { where: "where UslugaType_id = 2 and Usluga_id = " + usluga_id + " " }
				});
			}
		}
		else
		{			
			combo.setLoadQuery(false);
			// получаем уже использованные услуги
			var UsedDispUslugaTeen14Type = this.UsedDispUslugaTeen14Type;
			var UsedDispUslugaTeen14Type_Code = this.UsedDispUslugaTeen14Type_Code;
			var sex_id = this.Sex_id;
			//var fin = Array();
			var Usluga_Array = Array('"02000100"', '"02000130"', '"02001114"', '"02001307"','"02001353"', '"02001319"', '"02001352"');
			var Usluga_Array_Mongo = Array('02000100', '02000130', '02001114', '02001307','02001353', '02001319', '02001352');

			/*for ( Usluga_id in UsedDispUslugaTeen14Type )
			{
				//alert(Usluga_id);
				if ((Usluga_id > 0) && (Usluga_id != combo.getValue()))
				{
					fin.push(Usluga_id);
				}
			}*/

			for ( Usluga_Code in UsedDispUslugaTeen14Type_Code )
			{
				Usluga_Array.remove('"'+Usluga_Code+'"');
				Usluga_Array_Mongo.remove(Usluga_Code);
			}


			//var clause = '';
			//var sex_limit = "";
			if ( sex_id == 1 ) {
				//sex_limit = " and Usluga_Code != '02001353' ";
				Usluga_Array.remove('"02001353"');
				Usluga_Array_Mongo.remove('02001353');
				//clause = '&& (record["Usluga_Code"] != "02001353")';
			}
			if ( sex_id == 2 ) {
				//sex_limit = " and Usluga_Code != '02001319' ";
				Usluga_Array.remove('"02001319"');
				Usluga_Array_Mongo.remove('02001319');
				//clause = '&& (record["Usluga_Code"] != "02001319")';
			}
			/*var now_usluga = "";
			var now_usluga_idb = "";
			if ( usluga_id > 0 ) {
				now_usluga = " or Usluga_id = " + usluga_id;
				now_usluga_idb = ' || (record["Usluga_id"] == ' + usluga_id+') ';
				if ( sex_id == 2 ) {
					sex_limit = " and Usluga_Code != '02001319' ";
					now_usluga_idb = '&& (record["Usluga_Code"] != "02001319")';
				}
			}*/
			combo.getStore().removeAll();
			combo.clearValue();
			combo.lastQuery = '';
			combo.setLoadQuery(false);
			combo.getStore().clearFilter();
			combo.getStore().load(
			{
				callback: function() 
				{
					var fs = false;
					combo.getStore().each(function(record) 
					{
						//if ((combo.getStore().getCount()==1) && (usluga_id==''))
						//{
							//usluga_id = record.get('Usluga_id');
							//log('1 '+usluga_id);
						//}
						//log(record.get('Usluga_id')+' = '+usluga_id);
						if (record.get('Usluga_id') == usluga_id)
						{
							combo.setValue(usluga_id);
							combo.fireEvent('select', combo, record, 0);
							fs = true;
						}
					});
					if (!fs) 
					{
						combo.clearValue();
						combo.clearInvalid();
					}
					},
				params: {
					//where: "where (UslugaType_id = 2 " + sex_limit + " and Usluga_Code in ( '02000100', '02000130', '02001114', '02001307', /*'02240114', '02001322',*/ '02001353', '02001319', '02001352' ) and  Usluga_id not in ( 0 " + (fin.length > 0 ? ', ' : '') + fin.join(',') + " )) " + now_usluga + " ",
					where: "where UslugaType_id = 2 and Usluga_Code in (" + Usluga_Array_Mongo.join(', ') + ")",
					//clause: { where: 'record["UslugaType_id"] == 2 ' + clause + ' && (record["Usluga_Code"].inlist(["02000100", "02000130", "02001114", "02001307", "02001353", "02001319", "02001352"])) &&  (!record["Usluga_id"].inlist(["0" ' + (fin.length > 0 ? ', ' : '') + fin.join(',') + " ])) " + now_usluga_idb + " ", limit: null}
					clause: { where: 'record["UslugaType_id"] == 2 && record["Usluga_Code"].inlist(['+Usluga_Array+'])', limit: null}
				}
			});
			/*var DispUslugaTeen14Type_combo = this.findById('EUDT14EW_DispUslugaTeen14TypeCombo');
			DispUslugaTeen14Type_combo.lastQuery='';
			DispUslugaTeen14Type_combo.getStore().filterBy(function(record) {
				if ( 
					( record.data.DispUslugaTeen14Type_id == 5 &&  sex_id == 1 )
					|| ( record.data.DispUslugaTeen14Type_id == 8 &&  sex_id == 2 )
				)
					return false;
				return UsedDispUslugaTeen14Type[record.data.DispUslugaTeen14Type_id] != 1;
			});*/
		}
		return true;
		var combo = Ext.getCmp('EUDT14EW_UslugaCombo');
		var usluga_id = combo.getValue();
		combo.setLoadQuery(false);
		
		var fin = '';
		var fin_idb = '';
		switch (newValue.toString())
		{

			case '1': fin = "Usluga_Code in ('02000100')"; fin_idb = 'record["Usluga_Code"] == "02000100"'; break;
			case '2': fin = "Usluga_Code in ('02000130')"; fin_idb = 'record["Usluga_Code"] == "02000130"'; break;
			case '3': fin = "Usluga_Code in ('02001114')"; fin_idb = 'record["Usluga_Code"] == "02001114"'; break;
			case '4': fin = "Usluga_Code in ('02001307')"; fin_idb = 'record["Usluga_Code"] == "02001307"'; break; 
//			case '6': fin = "Usluga_Code in ('02240114')"; break;
//			case '7': fin = "Usluga_Code in ('02001322')"; break;
			case '5': fin = "Usluga_Code in ('02001353')"; fin_idb = 'record["Usluga_Code"] == "02001353"'; break;
			case '8': fin = "Usluga_Code in ('02001319')"; fin_idb = 'record["Usluga_Code"] == "02001319"'; break;
			case '9': fin = "Usluga_Code in ('02001352')"; fin_idb = 'record["Usluga_Code"] == "02001352"'; break;
			/*default: fin = "Usluga_Code in ('02000101', '02000401', '02000456', '02000410', '02003623', '02003624', '02000403','02000406','02000435',"+
			"'02000423','02000071', '02000432','02000130','02000592','02000593','02001101','02002301', '02002230', '02003316')"; break;*/
		}
		var combo = Ext.getCmp('EUDT14EW_UslugaCombo');
		var usluga_id = combo.getValue();
		combo.setLoadQuery(false);
		//log(fin);
		combo.getStore().load(
		{
			callback: function()
			{
				var fs = false;
				combo.getStore().each(function(record) 
				{
					//if ((combo.getStore().getCount()==1) && (usluga_id==''))
					//{
						usluga_id = record.get('Usluga_id');
						//log('1 '+usluga_id);
						combo.setValue(usluga_id);
					//}
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
			params: { where: "where UslugaType_id = 2 && "+fin+" ", clause: { where: 'record["UslugaType_id"] == 2 && ' + fin_idb, limit: null } }
		});
		
		var current_window = this;
		var set_date = this.findById('EUDT14EW_EvnUslugaDispTeen14_setDate').getValue();
		if ( !set_date || set_date == '' )
			var age = this.age;
		else
		{			
			var birth_date = this.Person_Birthday;
			var age = (birth_date.getMonthsBetween(set_date) - (birth_date.getMonthsBetween(set_date) % 12)) / 12;			
		}
		var UsedDispUslugaTeen14Type = this.UsedDispUslugaTeen14Type;
		var sex_id = this.Sex_id;
		var DispUslugaTeen14Type_combo = this.findById('EUDT14EW_DispUslugaTeen14TypeCombo');
		DispUslugaTeen14Type_combo.lastQuery='';
		DispUslugaTeen14Type_combo.getStore().filterBy(function(record) {
			if ( 
				( record.data.DispUslugaTeen14Type_id == 5 &&  sex_id == 1 )
				|| ( record.data.DispUslugaTeen14Type_id == 8 &&  sex_id == 2 )
			)
				return false;
			return UsedDispUslugaTeen14Type[record.data.DispUslugaTeen14Type_id] != 1;
		});
	},
    show: function() {
		sw.Promed.swEvnUslugaDispTeen14EditWindow.superclass.show.apply(this, arguments);

		var current_window = this;
		//alert(arguments[0].formParams['EvnUslugaDispTeen14_id']);
		//this.findById('EUDT14EW_DispUslugaTeen14TypeCombo').setValue(arguments[0].formParams['EvnUslugaDispTeen14_id']);
		//alert(this.findById('EUDT14EW_DispUslugaTeen14TypeCombo').getValue());


		current_window.restore();
		current_window.center();

        var form = current_window.findById('EvnUslugaDispTeen14EditForm');		
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

		/*var lpu_section_combo = this.findById('EUDT14EW_LpuSectionCombo');
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

		var med_personal_combo = this.findById('EUDT14EW_MedPersonalCombo');
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
		
		this.EvnPLDispTeen14_IsNotMammograf = false;
		if (arguments[0].EvnPLDispTeen14_IsNotMammograf)
		{
			this.EvnPLDispTeen14_IsNotMammograf = arguments[0].EvnPLDispTeen14_IsNotMammograf;
		}
		
		this.EvnPLDispTeen14_IsNotCito = false;
		if (arguments[0].EvnPLDispTeen14_IsNotCito)
		{
			this.EvnPLDispTeen14_IsNotCito = arguments[0].EvnPLDispTeen14_IsNotCito;
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

		current_window.findById('EUDT14EW_PersonInformationFrame').load({
			Person_id: (arguments[0].Person_id ? arguments[0].Person_id : ''),
			Person_Birthday: (arguments[0].Person_Birthday ? arguments[0].Person_Birthday : ''),
			Person_Firname: (arguments[0].Person_Firname ? arguments[0].Person_Firname : ''),
			Person_Secname: (arguments[0].Person_Secname ? arguments[0].Person_Secname : ''),
			Person_Surname: (arguments[0].Person_Surname ? arguments[0].Person_Surname : ''),
			callback: function() {
				var field = base_form.findField('EvnUslugaDispTeen14_setDate');
				clearDateAfterPersonDeath('personpanelid', 'EUDT14EW_PersonInformationFrame', field);
			}
		});

  		var loadMask = new Ext.LoadMask(Ext.get('EvnUslugaDispTeen14EditWindow'), { msg: LOAD_WAIT });
		loadMask.show();
		
		// чистим фильтр
		this.findById('EUDT14EW_DispUslugaTeen14TypeCombo').getStore().clearFilter();

        form.getForm().setValues(arguments[0].formParams);
        form.getForm().clearInvalid();

		var sex_id = arguments[0].Sex_id;
		var age = arguments[0].Person_Age;
		
		var usluga_combo = current_window.findById('EUDT14EW_UslugaCombo');
		
		var med_personal_id = arguments[0].formParams.MedPersonal_id;
		
		this.UsedDispUslugaTeen14Type = arguments[0].formParams['UsedDispUslugaTeen14Type'];
		this.UsedDispUslugaTeen14Type_Code = arguments[0].formParams['UsedDispUslugaTeen14Type_Code'];
		this.age = arguments[0].Person_Age;
		this.Sex_id = arguments[0].Sex_id;
		this.Person_Birthday = arguments[0].Person_Birthday;
		
		//загрузка таблицы(grid) с показателями		
		var dataset_num = arguments[0].formParams.RateGrid_DataNumber && arguments[0].formParams.RateGrid_DataNumber != "" ? arguments[0].formParams.RateGrid_DataNumber : 0; //проверяем есть ли номер датасета для данной услуги		
		this.ViewUslugaPokaz.clear();
		if(dataset_num > 0){
			this.ViewUslugaPokaz.restoreGridCopy(dataset_num);
		} else {
			if (arguments[0].formParams.EvnUslugaDispTeen14_id && current_window.action != 'add')
				this.ViewUslugaPokaz.loadData({rate_type: 'evnusluga', rate_subid: arguments[0].formParams.EvnUslugaDispTeen14_id}); //загрузка показателей для услуги
		}
		
		
        switch (current_window.action)
        {
            case 'add':
                current_window.setTitle(lang['laboratornoe_issledovanie_dobavlenie']);
                current_window.enableEdit(true);
				
				Ext.getCmp('EUDT14EW_ExaminationPlaceCombo').getStore().load({
					callback: function() {
						Ext.getCmp('EUDT14EW_ExaminationPlaceCombo').setValue(1);
					}
				});
				// Фильтруем виды исследований, показываем только незанятые
				var UsedDispUslugaTeen14Type = arguments[0].formParams['UsedDispUslugaTeen14Type'];
				var UsedDispUslugaTeen14Type_Code = arguments[0].formParams['UsedDispUslugaTeen14Type_Code'];
				var DispUslugaTeen14Type_combo = this.findById('EUDT14EW_DispUslugaTeen14TypeCombo');
				DispUslugaTeen14Type_combo.lastQuery='';
				DispUslugaTeen14Type_combo.getStore().filterBy(function(record) {					
					if ( 
						( record.data.DispUslugaTeen14Type_id == 5 && sex_id == 1 ) 
						|| ( record.data.DispUslugaTeen14Type_id == 8 && sex_id == 2 ) 
					)
						return false;					
					return UsedDispUslugaTeen14Type[record.data.DispUslugaTeen14Type_id] != 1;
				});
				this.findById('EUDT14EW_StudyTypeCombo').setValue(1);
				loadMask.hide();
				current_window.findById('EUDT14EW_EvnUslugaDispTeen14_setDate').focus(false, 250);
				this.setUslugaFilter();
                break;				
        	case 'edit':
        	    current_window.setTitle(lang['laboratornoe_issledovanie_redaktirovanie']);
                current_window.enableEdit(true);				
				this.start_usluga_val = usluga_combo.getValue();				
				current_window.findById('EUDT14EW_ExaminationPlaceCombo').fireEvent('change', current_window.findById('EUDT14EW_ExaminationPlaceCombo'), current_window.findById('EUDT14EW_ExaminationPlaceCombo').getValue());
				// Фильтруем виды исследований, показываем только незанятые
				var UsedDispUslugaTeen14Type = arguments[0].formParams['UsedDispUslugaTeen14Type'];
				var UsedDispUslugaTeen14Type_Code = arguments[0].formParams['UsedDispUslugaTeen14Type_Code'];
				var DispUslugaTeen14Type_combo = this.findById('EUDT14EW_DispUslugaTeen14TypeCombo');
				DispUslugaTeen14Type_combo.lastQuery='';
				DispUslugaTeen14Type_combo.getStore().filterBy(function(record) {
					if ( 
						( record.data.DispUslugaTeen14Type_id == 5 && sex_id == 1 ) 
						|| ( record.data.DispUslugaTeen14Type_id == 8 && sex_id == 2 )
					)
						return false;
					return UsedDispUslugaTeen14Type[record.data.DispUslugaTeen14Type_id]!=1;
				});
				this.setUslugaFilter();
				/*var usluga_id = usluga_combo.getValue();
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
				}*/

				// устанавливаем врача
				current_window.findById('EUDT14EW_MedPersonalCombo').getStore().findBy(function(record) {
					if ( record.get('MedPersonal_id') == med_personal_id )
					{						
						current_window.findById('EUDT14EW_MedPersonalCombo').setValue(record.get('MedStaffFact_id'));
						return true;
					}
				});
				this.first_msf = false;
				
				Ext.getCmp('EUDT14EW_ExaminationPlaceCombo').getStore().load({
					callback: function() {
						Ext.getCmp('EUDT14EW_ExaminationPlaceCombo').setValue(Ext.getCmp('EUDT14EW_ExaminationPlaceCombo').getValue());
					}
				});
				
				loadMask.hide();
				current_window.findById('EUDT14EW_EvnUslugaDispTeen14_setDate').fireEvent('change', current_window.findById('EUDT14EW_EvnUslugaDispTeen14_setDate'), current_window.findById('EUDT14EW_EvnUslugaDispTeen14_setDate').getValue());
				// устанавливаем врача
				current_window.findById('EUDT14EW_MedPersonalCombo').getStore().findBy(function(record) {
					if ( record.get('MedPersonal_id') == med_personal_id )
					{						
						current_window.findById('EUDT14EW_MedPersonalCombo').setValue(record.get('MedStaffFact_id'));
						return true;
					}
				});
				current_window.findById('EUDT14EW_EvnUslugaDispTeen14_setDate').fireEvent('change', current_window.findById('EUDT14EW_EvnUslugaDispTeen14_setDate'), current_window.findById('EUDT14EW_EvnUslugaDispTeen14_setDate').getValue());
				current_window.findById('EUDT14EW_EvnUslugaDispTeen14_setDate').focus(false, 250);
                break;

            case 'view':
                current_window.setTitle(lang['laboratornoe_issledovanie_prosmotr']);
                current_window.enableEdit(false);
				this.start_usluga_val = usluga_combo.getValue();
				current_window.findById('EUDT14EW_ExaminationPlaceCombo').fireEvent('change', current_window.findById('EUDT14EW_ExaminationPlaceCombo'), current_window.findById('EUDT14EW_ExaminationPlaceCombo').getValue());
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
				current_window.findById('EUDT14EW_MedPersonalCombo').getStore().findBy(function(record) {
					if ( record.get('MedPersonal_id') == med_personal_id )
					{
						current_window.findById('EUDT14EW_MedPersonalCombo').setValue(record.get('MedStaffFact_id'));
						return true;
					}
				});
				current_window.findById('EUDT14EW_EvnUslugaDispTeen14_setDate').fireEvent('change', current_window.findById('EUDT14EW_EvnUslugaDispTeen14_setDate'), current_window.findById('EUDT14EW_EvnUslugaDispTeen14_setDate').getValue());
				loadMask.hide();
				current_window.buttons[1].focus();
			break;
        }

        form.getForm().clearInvalid();
    },
    width: 700
});