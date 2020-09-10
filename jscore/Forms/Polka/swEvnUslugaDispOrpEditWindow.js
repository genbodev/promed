/**
* swEvnUslugaDispOrpEditWindow - окно редактирования/добавления выполнения лабораторного исследования по диспасеризации детей сирот детей-сирот
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
* @comment        Префикс для id компонентов eudoew (swEvnUslugaDispOrpEditWindow)
*	                tabIndex: TABINDEX_EUDOEF = 9300
*
*
* Использует: окно редактирования талона по диспасеризации детей сирот (swEvnPLDispOrpEditWindow)
*/

sw.Promed.swEvnUslugaDispOrpEditWindow = Ext.extend(sw.Promed.BaseForm, {
	action: null,
	buttonAlign: 'left',
	buttons: [{
		handler: function() {
			this.ownerCt.doSave();
		},
		iconCls: 'save16',
		id: 'eudoewSaveButton',
		tabIndex: TABINDEX_EUDOEF+7,
		text: BTN_FRMSAVE
	}, {
		handler: function() 
		{
			this.ownerCt.hide();
		},
		iconCls: 'cancel16',
		id: 'eudoewCancelButton',
		onTabAction: function() {
			Ext.getCmp('eudoewEvnUslugaDispOrp_setDate').focus(true, 200);
		},
		onShiftTabAction: function() {
			Ext.getCmp('eudoewSaveButton').focus(true, 200);
		},
		tabIndex: TABINDEX_EUDOEF+8,
		text: BTN_FRMCANCEL
	}, 
		HelpButton(this, TABINDEX_EUDOEF+9)
	],
    callback: Ext.emptyFn,
    closable: true,
    closeAction: 'hide',
    collapsible: true,
	doSave: function() {
		var add_flag = true;
		var current_window = this;
		var index = -1;
		var orpdispuslugatype_id = current_window.findById('eudoewOrpDispUslugaTypeCombo').getValue();
		var orpdispuslugatype_name = '';
		var lpu_section_id = current_window.findById('eudoewLpuSectionCombo').getValue();
		var lpu_section_name = '';
		var med_staff_fact_id = current_window.findById('eudoewMedPersonalCombo').getValue();
		var med_personal_fio = '';
		var usluga_id = current_window.findById('eudoewUslugaCombo').getValue();
		var usluga_code = '';
		var usluga_name = '';
		var examination_place_id = current_window.findById('eudoewExaminationPlaceCombo').getValue();
		var record_status = current_window.findById('eudoewRecord_Status').getValue();
			
		if (!current_window.findById('EvnUslugaDispOrpEditForm').getForm().isValid())
		{
            Ext.MessageBox.show({
                buttons: Ext.Msg.OK,
                fn: function() {
                	current_window.findById('eudoewEvnUslugaDispOrp_setDate').focus(false);
                },
                icon: Ext.Msg.WARNING,
                msg: ERR_INVFIELDS_MSG,
                title: ERR_INVFIELDS_TIT
            });
            return false;
		}
		
		if ( current_window.findById('eudoewEvnUslugaDispOrp_setDate').getValue() > current_window.findById('eudoewEvnUslugaDispOrp_didDate').getValue() )
		{
            Ext.MessageBox.show({
                buttons: Ext.Msg.OK,
                fn: function() {
                	current_window.findById('eudoewEvnUslugaDispOrp_setDate').focus(false);
                },
                icon: Ext.Msg.WARNING,
                msg: lang['data_issledovaniya_ne_mojet_prevyishat_datu_polucheniya_rezultata'],
                title: lang['oshibka']
            });
            return false;
		}
		
		var set_date = current_window.findById('eudoewEvnUslugaDispOrp_setDate').getValue();
		var did_date = current_window.findById('eudoewEvnUslugaDispOrp_didDate').getValue();		
		if ( ( set_date.getMonthsBetween(did_date) > 3 ) || ( set_date.getMonthsBetween(did_date) == 3 && (set_date.getDate() != did_date.getDate()) ) )
		{
			Ext.MessageBox.show({
                buttons: Ext.Msg.OK,
                fn: function() {
                	current_window.findById('eudoewEvnUslugaDispOrp_setDate').focus(false);
                },
                icon: Ext.Msg.WARNING,
                msg: lang['data_polucheniya_rezultata_laboratornogo_issledovaniya_ne_bolee_3-h_mesyatsev_s_datyi_issledovaniya'],
                title: lang['oshibka']
            });
            return false;
		}
		
		var pl_set_date = current_window.set_date;
		var set_date = current_window.findById('eudoewEvnUslugaDispOrp_setDate').getValue();
		var usluga_type_id = Ext.getCmp('eudoewOrpDispUslugaTypeCombo').getValue();
		if ( pl_set_date > set_date && (usluga_type_id == 6 || usluga_type_id == 5) )
		{
			if ( ( set_date.getMonthsBetween(pl_set_date) > 24 ) || ( set_date.getMonthsBetween(pl_set_date) == 24 && (set_date.getDate() != pl_set_date.getDate()) ) )
			{
				Ext.MessageBox.show({
					buttons: Ext.Msg.OK,
					fn: function() {
						current_window.findById('eudoewEvnUslugaDispOrp_setDate').focus(false);
					},
					icon: Ext.Msg.WARNING,
					msg: lang['davnost_etogo_issledovaniya_ne_mojet_byit_bolee_2h_let'],
					title: lang['oshibka']
				});
				return false;
			}
		}
		
		if ( pl_set_date > set_date && usluga_type_id != 6 && usluga_type_id != 5 )
		{
			if ( ( set_date.getMonthsBetween(pl_set_date) > 3 ) || ( set_date.getMonthsBetween(pl_set_date) == 3 && (set_date.getDate() != pl_set_date.getDate()) ) )
			{
				Ext.MessageBox.show({
					buttons: Ext.Msg.OK,
					fn: function() {
						current_window.findById('eudoewEvnUslugaDispOrp_setDate').focus(false);
					},
					icon: Ext.Msg.WARNING,
					msg: lang['davnost_etogo_issledovaniya_ne_mojet_byit_bolee_3h_mesyatsev'],
					title: lang['oshibka']
				});
				return false;
			}
		}
		
		if (record_status == 1)
		{
			record_status = 2;
		}

		index = current_window.findById('eudoewOrpDispUslugaTypeCombo').getStore().findBy(function(rec) { return rec.get('OrpDispUslugaType_id') == orpdispuslugatype_id; });
		
		if (index >= 0)
		{
			orpdispuslugatype_name = current_window.findById('eudoewOrpDispUslugaTypeCombo').getStore().getAt(index).data.OrpDispUslugaType_Name;
		}
		
		index = current_window.findById('eudoewLpuSectionCombo').getStore().findBy(function(rec) { return rec.get('LpuSection_id') == lpu_section_id; });
		
		if (index >= 0)
		{
			lpu_section_name = current_window.findById('eudoewLpuSectionCombo').getStore().getAt(index).data.LpuSection_Name;
		}

		var med_personal_fio = '';
		var med_personal_id = null;
		
		record = current_window.findById('eudoewMedPersonalCombo').getStore().getById(med_staff_fact_id);
		
		if ( record ) {
			med_personal_fio = record.get('MedPersonal_Fio');
			med_personal_id = record.get('MedPersonal_id');
		}

		index = current_window.findById('eudoewUslugaCombo').getStore().findBy(function(rec) { return rec.get('Usluga_id') == usluga_id; });
		if (index >= 0)
		{
			usluga_code = current_window.findById('eudoewUslugaCombo').getStore().getAt(index).data.Usluga_Code;
			usluga_name = current_window.findById('eudoewUslugaCombo').getStore().getAt(index).data.Usluga_Name;
		}
		
		if (current_window.action != 'add')
		{
			add_flag = false;
		}
		//log(orpdispuslugatype_id);
		//log(orpdispuslugatype_name);
		var data = [{
			'EvnUslugaDispOrp_id': current_window.findById('eudoewEvnUslugaDispOrp_id').getValue(),
			'EvnUslugaDispOrp_setDate': current_window.findById('eudoewEvnUslugaDispOrp_setDate').getValue(),
			'EvnUslugaDispOrp_didDate': current_window.findById('eudoewEvnUslugaDispOrp_didDate').getValue(),
			'OrpDispUslugaType_id': orpdispuslugatype_id,
			'OrpDispUslugaType_Name': orpdispuslugatype_name,
			'LpuSection_id': lpu_section_id,
			'ExaminationPlace_id': examination_place_id,
			'LpuSection_Name': lpu_section_name,
			'MedPersonal_id': med_personal_id,
			'MedPersonal_Fio': med_personal_fio,
			'Usluga_id': usluga_id,
			'Usluga_Code': usluga_code,
			'Usluga_Name': usluga_name,
			'Record_Status': record_status
		}];
		current_window.callback(data, add_flag);
		current_window.hide();
    },
	draggable: true,
    enableEdit: function(enable) {
    	if (enable)
    	{
			this.findById('eudoewEvnUslugaDispOrp_setDate').enable();
			this.findById('eudoewEvnUslugaDispOrp_didDate').enable();
			this.findById('eudoewOrpDispUslugaTypeCombo').enable();
			this.findById('eudoewMedPersonalCombo').enable();
			this.findById('eudoewLpuSectionCombo').enable();
			this.findById('eudoewUslugaCombo').enable();
			// enable() для кнопок на гридах

			this.buttons[0].enable();
		}
		else
    	{
			this.findById('eudoewEvnUslugaDispOrp_setDate').disable();
			this.findById('eudoewEvnUslugaDispOrp_didDate').disable();
			this.findById('eudoewOrpDispUslugaTypeCombo').disable();
			this.findById('eudoewMedPersonalCombo').disable();
			this.findById('eudoewLpuSectionCombo').disable();
			this.findById('eudoewUslugaCombo').disable();

			// disable() для кнопок на гридах

			this.buttons[0].disable();
		}
    },
    height: 300,
	id: 'EvnUslugaDispOrpEditWindow',
    initComponent: function() {
        Ext.apply(this, {
            items: [
				new	sw.Promed.PersonInformationPanelShort({
					id: 'eudoewPersonInformationFrame',
					region: 'north'
				}),
				new Ext.form.FormPanel({
					bodyBorder: false,
					bodyStyle: 'padding: 5px 5px 0',
					border: false,
					frame: false,
					id: 'EvnUslugaDispOrpEditForm',
					labelAlign: 'right',
					labelWidth: 150,
					items: [{
						id: 'eudoewEvnUslugaDispOrp_id',
						name: 'EvnUslugaDispOrp_id',
						value: 0,
						xtype: 'hidden'
					}, {
						id: 'eudoewRecord_Status',
						name: 'Record_Status',
						value: 0,
						xtype: 'hidden'
					}, {
						allowBlank: false,
						enableKeyEvents: true,
						fieldLabel: lang['data_issledovaniya'],
						format: 'd.m.Y',
						id: 'eudoewEvnUslugaDispOrp_setDate',
						listeners: {
							'keydown':  function(inp, e) {
								if ( e.shiftKey && e.getKey() == Ext.EventObject.TAB )
								{
									e.stopEvent();
									Ext.getCmp('eudoewCancelButton').focus(true, 200);
								}
							},
							'change': function(field, newValue, oldValue) {
								if (blockedDateAfterPersonDeath('personpanelid', 'eudoewPersonInformationFrame', field, newValue, oldValue)) return;
								if ( newValue > 0 )
								{
									this.setOrpDispUslugaTypeFilter();

									//http://redmine.swan.perm.ru/issues/12765
									//Логика следующая. Листенер Change вызывается в двух случаях - при изменении даты начала
									//и при выполнении fireEvent в Show. Нужно было сделать так, что при открытии на просмотр
									//или изменение исследования обновление даты результата не производилось.
									//Поэтому была сделана проверка на тип OldValue, т.к. в случае изменения даты начала
									//у этой переменной тип - либо дата (если какая-то дата результата была уже проставлена,
									//и меняем дату исследования, либо строка (если добавляем новое исследование, и дата окончания
									// еще была пустой). И только(!) в случае открытия на просмотр или изменения
									// у oldValue тип - undefined (т.к. в fireEvent этот параметр не передается).
									if (typeof(oldValue)!='undefined')
										Ext.getCmp('eudoewEvnUslugaDispOrp_didDate').setValue(newValue);


									var base_form = this.findById('EvnUslugaDispOrpEditForm').getForm();

									var lpu_section_id = base_form.findField('LpuSection_id').getValue();
									var med_staff_fact_id = base_form.findField('MedStaffFact_id').getValue();

									base_form.findField('LpuSection_id').clearValue();
									base_form.findField('MedStaffFact_id').clearValue();
																	
									var params = {
										//isPolka: true
									}
									
									if ( newValue )
									{
										params.onDate = Ext.util.Format.date(newValue, 'd.m.Y');
										//if (this.action == 'add' || this.action == 'edit')
										if (this.action == 'add')
											base_form.findField('Usluga_id').setFilterActualUsluga(newValue, null);
									}
									
									// параклиника
									params.arrayLpuUnitType = ['6'];
									
									setLpuSectionGlobalStoreFilter(params);
									setMedStaffFactGlobalStoreFilter(params);

									base_form.findField('LpuSection_id').getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));
									base_form.findField('MedStaffFact_id').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));

									if ( base_form.findField('LpuSection_id').getStore().getById(lpu_section_id) ) {
										base_form.findField('LpuSection_id').setValue(lpu_section_id);
									}
								
									if ( base_form.findField('MedStaffFact_id').getStore().getById(med_staff_fact_id) ) {
										base_form.findField('MedStaffFact_id').setValue(med_staff_fact_id);
									}
								}
							}.createDelegate(this)						
						},
						name: 'EvnUslugaDispOrp_setDate',
						maxValue: Date.parseDate(getGlobalOptions().date, 'd.m.Y'),
						plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
						tabIndex: TABINDEX_EUDOEF+1,
						width: 100,
						xtype: 'swdatefield'
					}, {
						allowBlank: false,
						fieldLabel: lang['data_rezultata'],
						format: 'd.m.Y',
						id: 'eudoewEvnUslugaDispOrp_didDate',
						name: 'EvnUslugaDispOrp_didDate',
						maxValue: Date.parseDate(getGlobalOptions().date, 'd.m.Y'),
						plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
						tabIndex: TABINDEX_EUDOEF+2,
						width: 100,
						xtype: 'swdatefield'
					}, {
						allowBlank: false,
						enableKeyEvents: true,
						fieldLabel: lang['vid'],
						id: 'eudoewOrpDispUslugaTypeCombo',
						listeners: {
							'change': function(field, newValue, oldValue) 
							{
								var fin = '';
								switch (newValue.toString())
								{
									
									case '1': fin = "Usluga_Code in ('02000101')"; break; 
									case '2': fin = "Usluga_Code in ('02000130')"; break; 
									case '3': fin = "Usluga_Code in ('02001101')"; break; 
									case '4': fin = "Usluga_Code in ('02001315')"; break; 
									case '5': fin = "Usluga_Code in ('02001304')"; break; 
									case '6': fin = "Usluga_Code in ('02001301')"; break; 
									case '7': fin = "Usluga_Code in ('02001311')"; break; 
								}
								var combo = Ext.getCmp('eudoewUslugaCombo');
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
						name: 'OrpDispUslugaType_id',
						tabIndex: TABINDEX_EUDOEF+3,
						validateOnBlur: false,
						width: 350,
						xtype: 'sworpdispuslugatypecombo'
					}, {
						allowBlank: false,
						enableKeyEvents: true,
						id: 'eudoewExaminationPlaceCombo',
						listeners: 
						{
							'change': function(field, newValue, oldValue) 
							{
								// Проверка на выбранное место выполненения 
								// Если исследование проведено в «В своем МУ», в поле "Отделение" погружать все отделения своего МУ (как сейчас)
								// Если исследование проведено «В стационаре», поле Отделение делать пустым и неактивным
								// Если исследование проведено «В другом МУ», поле Отделение делать пустым и неактивным
								// http://172.19.61.14:81/issues/show/1594
								// скопированно с ДД вместе с камментами
								
								var fin = '';
								var LpuSectionCombo = Ext.getCmp('eudoewLpuSectionCombo');
								var MedPersonalCombo = Ext.getCmp('eudoewMedPersonalCombo');
								if (newValue.inlist([2,3]))
								{
									LpuSectionCombo.setDisabled(true);
									LpuSectionCombo.setValue(null);
									MedPersonalCombo.setDisabled(true);
									MedPersonalCombo.setValue(null);
								}
								else 
								{
									LpuSectionCombo.setDisabled(false);
									MedPersonalCombo.setDisabled(false);
								}
							}
						},
						name: 'ExaminationPlace_id',
						tabIndex: TABINDEX_EUDDEW+04,
						validateOnBlur: false,
						width: 350,
						xtype: 'swexaminationplacecombo'
					}, {
						allowBlank: true,
						hiddenName: 'LpuSection_id',
						id: 'eudoewLpuSectionCombo',
						lastQuery: '',
						listWidth: 650,
						linkedElements: [
							'eudoewMedPersonalCombo'
						],
						tabIndex: TABINDEX_EUDOEF+4,
						width: 450,
						xtype: 'swlpusectionglobalcombo'
					}, {
						allowBlank: true,
						hiddenName: 'MedStaffFact_id',
						id: 'eudoewMedPersonalCombo',
						lastQuery: '',
						listWidth: 650,
						parentElementId: 'eudoewLpuSectionCombo',
						tabIndex: TABINDEX_EUDOEF+5,
						width: 450,
						xtype: 'swmedstafffactglobalcombo'
					}, {
						allowBlank: false,
						id: 'eudoewUslugaCombo',
						listWidth: 500,
						tabIndex: TABINDEX_EUDOEF+6,
						width: 450,
						listeners: {
							'beforequery': function(event) {
								var usluga_date_field = this.findById('EvnUslugaDispOrpEditForm').getForm().findField('EvnUslugaDispOrp_setDate');
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
    	sw.Promed.swEvnUslugaDispOrpEditWindow.superclass.initComponent.apply(this, arguments);
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

        	var current_window = Ext.getCmp('EvnUslugaDispOrpEditWindow');

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
    minHeight: 300,
    minWidth: 700,
    modal: true,
    onHide: Ext.emptyFn,
	ownerWindow: null,
    plain: true,
    resizable: true,
	setOrpDispUslugaTypeFilter: function() {
		var set_date = this.findById('eudoewEvnUslugaDispOrp_setDate').getValue();
		if ( !set_date || set_date == '' )
			var age = this.age;
		else
		{			
			var birth_date = this.Person_Birthday;
			var age = (birth_date.getMonthsBetween(set_date) - (birth_date.getMonthsBetween(set_date) % 12)) / 12;			
		}
		var UsedOrpDispUslugaType = this.UsedOrpDispUslugaType;
		var orpdispuslugatype_combo = this.findById('eudoewOrpDispUslugaTypeCombo');
		orpdispuslugatype_combo.getStore().clearFilter();
		orpdispuslugatype_combo.lastQuery='';
		orpdispuslugatype_combo.getStore().filterBy(function(record) 
		{
			if (((record.data.OrpDispUslugaType_id == 7) && (age >= 1)))
				return false;
			return UsedOrpDispUslugaType[record.data.OrpDispUslugaType_id] != 1;
		});
	},
    show: function() {
		sw.Promed.swEvnUslugaDispOrpEditWindow.superclass.show.apply(this, arguments);

		var current_window = this;

		current_window.restore();
		current_window.center();

        var form = current_window.findById('EvnUslugaDispOrpEditForm');
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
		
		base_form.findField('MedStaffFact_id').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));

		/*var lpu_section_combo = this.findById('eudoewLpuSectionCombo');
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

		var med_personal_combo = this.findById('eudoewMedPersonalCombo');
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

		current_window.findById('eudoewPersonInformationFrame').load({
			Person_id: (arguments[0].Person_id ? arguments[0].Person_id : ''),
			Person_Birthday: (arguments[0].Person_Birthday ? arguments[0].Person_Birthday : ''),
			Person_Firname: (arguments[0].Person_Firname ? arguments[0].Person_Firname : ''),
			Person_Secname: (arguments[0].Person_Secname ? arguments[0].Person_Secname : ''),
			Person_Surname: (arguments[0].Person_Surname ? arguments[0].Person_Surname : ''),
			callback: function() {
				var field = base_form.findField('EvnUslugaDispOrp_setDate');
				clearDateAfterPersonDeath('personpanelid', 'eudoewPersonInformationFrame', field);
			}
		});

  		var loadMask = new Ext.LoadMask(Ext.get('EvnUslugaDispOrpEditWindow'), { msg: LOAD_WAIT });
		loadMask.show();
		
		// чистим фильтр
		this.findById('eudoewOrpDispUslugaTypeCombo').getStore().clearFilter();

        form.getForm().setValues(arguments[0].formParams);
        form.getForm().clearInvalid();

		var sex_id = arguments[0].Sex_id;
		var age = arguments[0].Person_Age;
		
		var usluga_combo = current_window.findById('eudoewUslugaCombo');
		
		var med_personal_id = arguments[0].formParams.MedPersonal_id;
		
		this.UsedOrpDispUslugaType = arguments[0].formParams['UsedOrpDispUslugaType'];
		this.age = arguments[0].Person_Age;
		this.Person_Birthday = arguments[0].Person_Birthday;
		
        switch (current_window.action)
        {
            case 'add':
                current_window.setTitle(lang['laboratornoe_issledovanie_dobavlenie']);
                current_window.enableEdit(true);

				// Фильтруем виды исследований, показываем только незанятые
				var UsedOrpDispUslugaType = arguments[0].formParams['UsedOrpDispUslugaType'];
				var orpdispuslugatype_combo = this.findById('eudoewOrpDispUslugaTypeCombo');
				orpdispuslugatype_combo.lastQuery='';
				orpdispuslugatype_combo.getStore().filterBy(function(record)
				{
					if (((record.data.OrpDispUslugaType_id == 7) && (age >= 1)))
						return false;
					return UsedOrpDispUslugaType[record.data.OrpDispUslugaType_id] != 1;
				});
				
				loadMask.hide();
				current_window.findById('eudoewEvnUslugaDispOrp_setDate').focus(false, 250);
				current_window.findById('eudoewExaminationPlaceCombo').setValue(1); //по умолчанию устанавливаем значение «В своем МУ»
				current_window.findById('eudoewExaminationPlaceCombo').fireEvent('change', current_window.findById('eudoewExaminationPlaceCombo'), current_window.findById('eudoewExaminationPlaceCombo').getValue());
				break;

        	case 'edit':
        	    current_window.setTitle(lang['laboratornoe_issledovanie_redaktirovanie']);
                current_window.enableEdit(true);
				current_window.findById('eudoewExaminationPlaceCombo').fireEvent('change', current_window.findById('eudoewExaminationPlaceCombo'), current_window.findById('eudoewExaminationPlaceCombo').getValue());
				// Фильтруем виды исследований, показываем только незанятые
				var UsedOrpDispUslugaType = arguments[0].formParams['UsedOrpDispUslugaType'];
				var OrpDispUslugaType_id = arguments[0].formParams['OrpDispUslugaType_id'];
				var orpdispuslugatype_combo = this.findById('eudoewOrpDispUslugaTypeCombo');
				orpdispuslugatype_combo.lastQuery='';
				orpdispuslugatype_combo.getStore().filterBy(function(record) 
				{
					if (record.data.OrpDispUslugaType_id == OrpDispUslugaType_id)
						return true;
					if ( (record.data.OrpDispUslugaType_id != OrpDispUslugaType_id) && (record.data.OrpDispUslugaType_id == 7) && (age >= 1) )
						return false;
					return (UsedOrpDispUslugaType[record.data.OrpDispUslugaType_id] != 1);
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
				current_window.findById('eudoewMedPersonalCombo').getStore().clearFilter();
				current_window.findById('eudoewMedPersonalCombo').getStore().lastQuery = '';
				current_window.findById('eudoewMedPersonalCombo').getStore().findBy(function(record) {
					if ( record.get('MedPersonal_id') == med_personal_id )
					{						
						current_window.findById('eudoewMedPersonalCombo').setValue(record.get('MedStaffFact_id'));
						return true;
					}
				});
				loadMask.hide();
				current_window.findById('eudoewEvnUslugaDispOrp_setDate').fireEvent('change', current_window.findById('eudoewEvnUslugaDispOrp_setDate'), current_window.findById('eudoewEvnUslugaDispOrp_setDate').getValue());
				current_window.findById('eudoewEvnUslugaDispOrp_setDate').focus(false, 250);
				break;

            case 'view':
                current_window.setTitle(lang['laboratornoe_issledovanie_prosmotr']);
                current_window.enableEdit(false);
				current_window.findById('eudoewExaminationPlaceCombo').fireEvent('change', current_window.findById('eudoewExaminationPlaceCombo'), current_window.findById('eudoewExaminationPlaceCombo').getValue());
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
				current_window.findById('eudoewMedPersonalCombo').getStore().findBy(function(record) {
					if ( record.get('MedPersonal_id') == med_personal_id )
					{
						current_window.findById('eudoewMedPersonalCombo').setValue(record.get('MedStaffFact_id'));
						return true;
					}
				});
				current_window.findById('eudoewEvnUslugaDispOrp_setDate').fireEvent('change', current_window.findById('eudoewEvnUslugaDispOrp_setDate'), current_window.findById('eudoewEvnUslugaDispOrp_setDate').getValue());
				loadMask.hide();
				current_window.buttons[1].focus();

                break;
        }

        form.getForm().clearInvalid();
    },
    width: 700
});