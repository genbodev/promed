/**
* swEvnVizitDispOrpEditWindow - окно редактирования/добавления осмотра по диспасеризации детей-сирот
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package    Polka
* @access     public
* @copyright  Copyright (c) 2009 Swan Ltd.
* @author     Марков Андрей
* @version    май 2010
* @comment    Префикс для id компонентов EVDDEW (swEvnVizitDispOrpEditWindow)
*	            TABINDEX_EVDOEF: 9400
*
*
* Использует: окно редактирования талона по диспасеризации детей-сирот (swEvnPLDispOrpEditWindow)
*/
/*NO PARSE JSON*/
sw.Promed.swEvnVizitDispOrpEditWindow = Ext.extend(sw.Promed.BaseForm, 
{
	/* */
	codeRefresh: true,
	objectName: 'swEvnVizitDispOrpEditWindow',
	objectSrc: '/jscore/Forms/Polka/swEvnVizitDispOrpEditWindow.js',
	action: null,
	buttonAlign: 'left',
	buttons: [{
		handler: function() {
			this.ownerCt.doSave();
		},
		iconCls: 'save16',
		id: 'evdoewSaveButton',
		tabIndex: TABINDEX_EVDOEF+12,
		text: BTN_FRMSAVE
	}, {
		handler: function() {
			this.ownerCt.hide();
		},
		iconCls: 'cancel16',
		id: 'evdoewCancelButton',
		onTabAction: function() {
			Ext.getCmp('evdoewEvnVizitDispOrp_setDate').focus(true, 200);
		},
		onShiftTabAction: function() {
			Ext.getCmp('evdoewSaveButton').focus(true, 200);
		},
		tabIndex: TABINDEX_EVDOEF+13,
		text: BTN_FRMCANCEL
	}, 
		HelpButton(this)
	],
    callback: Ext.emptyFn,
    closable: true,
    closeAction: 'hide',
    collapsible: true,
	doSave: function() {
		var add_flag = true;
		var current_window = this;
		var index = -1;
		var orpdisp_spec_id = current_window.findById('evdoewOrpDispSpecCombo').getValue();
		var orpdisp_spec_name = '';
		var lpu_section_id = current_window.findById('evdoewLpuSectionCombo').getValue();
		var lpu_section_name = '';
		var med_staff_fact_id = current_window.findById('evdoewMedPersonalCombo').getValue();
		current_window.findById('evdoewDiagCombo').fireEvent('blur', current_window.findById('evdoewDiagCombo'));
		var diag_id = current_window.findById('evdoewDiagCombo').getValue();
		var diag_code = '';
		var orpdispdiagtype_id = current_window.findById('evdoewDopDispDiagTypeCombo').getValue();
		var deseasestage_id = current_window.findById('evdoewDeseaseStageCombo').getValue();
		var healthkind_id = current_window.findById('evdoewHealthKindCombo').getValue();
		var issankur_id = current_window.findById('evdoewEvnVizitDispOrp_IsSanKurCombo').getValue();
		var isout_id = current_window.findById('evdoewEvnVizitDispOrp_IsOutCombo').getValue();
		var isalien_id = Ext.getCmp('evdoewDopDispAlien_idCombo').getValue();
		
		var record_status = current_window.findById('evdoewRecord_Status').getValue();

		if (!current_window.findById('EvnVizitDispOrpEditForm').getForm().isValid())
		{
            Ext.MessageBox.show({
                buttons: Ext.Msg.OK,
                fn: function() {
                	current_window.findById('evdoewEvnVizitDispOrp_setDate').focus(false);
                },
                icon: Ext.Msg.WARNING,
                msg: ERR_INVFIELDS_MSG,
                title: ERR_INVFIELDS_TIT
            });
            return false;
		}
		
		if ( orpdisp_spec_id == 1 && (current_window.findById('evdoewEvnVizitDispOrp_setDate').getValue() < current_window.max_date) )
		{
			Ext.MessageBox.show({
                buttons: Ext.Msg.OK,
                fn: function() {
                	current_window.findById('evdoewEvnVizitDispOrp_setDate').focus(false);
                },
                icon: Ext.Msg.WARNING,
                msg: "Осмотр педиатра не может быть проведен ранее других осмотров или даты получения результатов исследований.",
                title: ERR_INVFIELDS_TIT
            });
            return false;		
		}
		
		var set_date = current_window.findById('evdoewEvnVizitDispOrp_setDate').getValue();
		if ( set_date < Date.parseDate('01.01.' + set_date.getFullYear(), 'd.m.Y') )
		{
			Ext.MessageBox.show({
                buttons: Ext.Msg.OK,
                fn: function() {
                	current_window.findById('evdoewEvnVizitDispOrp_setDate').focus(false);
                },
                icon: Ext.Msg.WARNING,
                msg: lang['data_nachala_ne_mojet_byit_menshe_01_01'] + set_date.getFullYear() + '.',
                title: lang['oshibka']
            });
            return false;
		}

		if (record_status == 1)
		{
			record_status = 2;
		}

		index = current_window.findById('evdoewOrpDispSpecCombo').getStore().findBy(function(rec) { return rec.get('OrpDispSpec_id') == orpdisp_spec_id; });
		if (index >= 0)
		{
			orpdisp_spec_name = current_window.findById('evdoewOrpDispSpecCombo').getStore().getAt(index).data.OrpDispSpec_Name;
		}
		
		index = current_window.findById('evdoewLpuSectionCombo').getStore().findBy(function(rec) { return rec.get('LpuSection_id') == lpu_section_id; });
		if (index >= 0)
		{
			lpu_section_name = current_window.findById('evdoewLpuSectionCombo').getStore().getAt(index).data.LpuSection_Name;
		}

		
		record = current_window.findById('evdoewMedPersonalCombo').getStore().getById(med_staff_fact_id);
		if ( record ) {
			med_personal_fio = record.get('MedPersonal_Fio');
			med_personal_id = record.get('MedPersonal_id');
		} else {
			med_personal_fio = null;
			med_personal_id = null;
		}				

		index = current_window.findById('evdoewDiagCombo').getStore().findBy(function(rec) { return rec.get('Diag_id') == diag_id; });
		if (index >= 0)
		{
			diag_code = current_window.findById('evdoewDiagCombo').getStore().getAt(index).data.Diag_Code;
		}
		
		if (current_window.action != 'add')
		{
			add_flag = false;
		}

		var data = [{
			'EvnVizitDispOrp_id': current_window.findById('evdoewEvnVizitDispOrp_id').getValue(),
			'OrpDispSpec_id': orpdisp_spec_id,
			'LpuSection_id': lpu_section_id,
			'MedPersonal_id': med_personal_id,
			'Diag_id': diag_id,
			'OrpDispSpec_Name': orpdisp_spec_name,
			'EvnVizitDispOrp_setDate': current_window.findById('evdoewEvnVizitDispOrp_setDate').getValue(),
			'LpuSection_Name': lpu_section_name,
			'MedPersonal_Fio': med_personal_fio,
			'Diag_Code': diag_code,
			'DopDispDiagType_id': orpdispdiagtype_id,
			'DeseaseStage_id': deseasestage_id,
			'HealthKind_id': healthkind_id,
			'EvnVizitDispOrp_IsSanKur': issankur_id,
			'EvnVizitDispOrp_IsOut': isout_id,
			'DopDispAlien_id': isalien_id,
			'Record_Status': record_status
		}];

		current_window.callback(data, add_flag);
		current_window.hide();
    },
	draggable: true,
    enableEdit: function(enable) {
    	if (enable)
    	{
			this.findById('evdoewEvnVizitDispOrp_setDate').enable();
			this.findById('evdoewLpuSectionCombo').enable();
			this.findById('evdoewOrpDispSpecCombo').enable();
			this.findById('evdoewMedPersonalCombo').enable();
			this.findById('evdoewDiagCombo').enable();
			this.findById('evdoewDopDispDiagTypeCombo').enable();
			this.findById('evdoewDeseaseStageCombo').enable();
			this.findById('evdoewHealthKindCombo').enable();
			this.findById('evdoewEvnVizitDispOrp_IsSanKurCombo').enable();
			this.findById('evdoewEvnVizitDispOrp_IsOutCombo').enable();
			Ext.getCmp('evdoewDopDispAlien_idCombo').enable();

			// enable() для кнопок на гридах

			this.buttons[0].enable();
		}
		else
    	{
			this.findById('evdoewEvnVizitDispOrp_setDate').disable();
			this.findById('evdoewLpuSectionCombo').disable();
			this.findById('evdoewOrpDispSpecCombo').disable();
			this.findById('evdoewMedPersonalCombo').disable();
			this.findById('evdoewDiagCombo').disable();
			this.findById('evdoewDopDispDiagTypeCombo').disable();
			this.findById('evdoewDeseaseStageCombo').disable();
			this.findById('evdoewHealthKindCombo').disable();
			this.findById('evdoewEvnVizitDispOrp_IsSanKurCombo').disable();
			this.findById('evdoewEvnVizitDispOrp_IsOutCombo').disable();
			Ext.getCmp('evdoewDopDispAlien_idCombo').disable();

			// disable() для кнопок на гридах

			this.buttons[0].disable();
		}
    },
    height: 390,
	id: 'EvnVizitDispOrpEditWindow',
    initComponent: function() {
        Ext.apply(this, {
            items: [
				new	sw.Promed.PersonInformationPanelShort({
					id: 'evdoewPersonInformationFrame',
					region: 'north'
				}),
				new Ext.form.FormPanel({
					bodyBorder: false,
					bodyStyle: 'padding: 5px 5px 0',
					border: false,
					frame: false,
					id: 'EvnVizitDispOrpEditForm',
					labelAlign: 'right',
					labelWidth: 150,
					items: [{
						id: 'evdoewEvnVizitDispOrp_id',
						name: 'EvnVizitDispOrp_id',
						value: 0,
						xtype: 'hidden'
					}, {
						id: 'evdoewRecord_Status',
						name: 'Record_Status',
						value: 0,
						xtype: 'hidden'
					}, {
						allowBlank: false,
						enableKeyEvents: true,
						fieldLabel: lang['data_osmotra'],
						format: 'd.m.Y',
						id: 'evdoewEvnVizitDispOrp_setDate',
						listeners: {
							'keydown':  function(inp, e) {
								if ( e.shiftKey && e.getKey() == Ext.EventObject.TAB )
								{
									e.stopEvent();
									Ext.getCmp('evdoewCancelButton').focus(true, 200);
								}
							},
							'change': function(field, newValue, oldValue) {
								if (blockedDateAfterPersonDeath('personpanelid', 'evdoewPersonInformationFrame', field, newValue, oldValue)) return;
							
								this.setOrpDispSpecFilter();
								this.selectSpecialist();
								/*
								if ( newValue > 0 )
								{
									var base_form = this.findById('EvnVizitDispOrpEditForm').getForm();

									var lpu_section_id = base_form.findField('LpuSection_id').getValue();
									var med_staff_fact_id = base_form.findField('MedStaffFact_id').getValue();
									
									var spec_id = Ext.getCmp('evdoewOrpDispSpecCombo').getValue();

									base_form.findField('LpuSection_id').clearValue();
									base_form.findField('MedStaffFact_id').clearValue();
																	
									var params = {
										isPolkaAndStom: true
									}
									
									if ( newValue )
									{
										if ( spec_id > 0 )
										{
											switch ( spec_id )
											{
												case 1:
													params.arrayLpuSectionProfile = ['0900'];
												break;
												case 2:
													params.arrayLpuSectionProfile = ['2800'];
												break;
												case 3:
													params.arrayLpuSectionProfile = ['2700'];
												break;
												case 4:
													params.arrayLpuSectionProfile = ['2350'];
												break;
												case 5:
													params.arrayLpuSectionProfile = ['2600'];
												break;
												case 6:
													params.arrayLpuSectionProfile = ['2517'];
												break;
												case 7:
													params.arrayLpuSectionProfile = ['1830', '1800'];
												break;
												case 8:
													params.arrayLpuSectionProfile = ['1450'];
												break;
												case 9:
													params.arrayLpuSectionProfile = ['3710'];
												break;
												case 10:
													params.arrayLpuSectionProfile = ['1530','1500','2350'];
												break;
												case 11:
													params.arrayLpuSectionProfile = ['0530', '0510'];
												break;
											}
										}
										
										if ( newValue )
										{
											params.onDate = Ext.util.Format.date(newValue, 'd.m.Y');
											params.onDate = Ext.util.Format.date(newValue, 'd.m.Y');
										}
									}
									
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
								*/
							}.createDelegate(this)
						},
						maxValue: Date.parseDate(getGlobalOptions().date, 'd.m.Y'),
						//minValue: Date.parseDate('01.01.' + Date.parseDate(getGlobalOptions().date, 'd.m.Y').getFullYear(), 'd.m.Y'),
						name: 'EvnVizitDispOrp_setDate',
						plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
						tabIndex: TABINDEX_EVDOEF + 1,
						width: 100,
						xtype: 'swdatefield'
					}, {
						allowBlank: false,
						enableKeyEvents: true,
						fieldLabel: lang['spetsialnost_vracha'],
						id: 'evdoewOrpDispSpecCombo',
						listeners: {
							'change': function(combo, newValue) {
								this.selectSpecialist();
							}.createDelegate(this)
						},
						name: 'OrpDispSpec_id',
						tabIndex: TABINDEX_EVDOEF + 2,
						validateOnBlur: false,
						width: 350,
						xtype: 'sworpdispspeccombo'
					},{
						allowBlank: false,
						fieldLabel: lang['storonniy_spetsialist'],
						hiddenName: 'DopDispAlien_id',
						id: 'evdoewDopDispAlien_idCombo',
						comboSubject: 'DopDispAlien',
						sortField: 'DopDispAlien_Code',
						tabIndex: TABINDEX_EVDOEF + 3,
						width: 200,
						xtype: 'swcustomobjectcombo',
						listeners: {
							'change': function(combo, newValue) {
								this.selectSpecialist();
							}.createDelegate(this)
						}
					}, {
						allowBlank: false,
						hiddenName: 'LpuSection_id',
						id: 'evdoewLpuSectionCombo',
						lastQuery: '',
						listWidth: 650,
						linkedElements: [
							'evdoewMedPersonalCombo'
						],
						tabIndex: TABINDEX_EVDOEF + 4,
						width: 450,
						xtype: 'swlpusectionglobalcombo'
					}, {
						allowBlank: false,
						hiddenName: 'MedStaffFact_id',
						id: 'evdoewMedPersonalCombo',
						lastQuery: '',
						listWidth: 650,
						parentElementId: 'evdoewLpuSectionCombo',
						tabIndex: TABINDEX_EVDOEF + 5,
						width: 450,
						xtype: 'swmedstafffactglobalcombo'
					}, {
						allowBlank: false,
						id: 'evdoewDiagCombo',
						listWidth: 580,
						tabIndex: TABINDEX_EVDOEF + 6,
						width: 450,
						xtype: 'swdiagcombo'
					}, {
						allowBlank: false,
						enableKeyEvents: true,
						fieldLabel: lang['zabolevanie'],
						id: 'evdoewDopDispDiagTypeCombo',
						name: 'DopDispDiagType_id',
						tabIndex: TABINDEX_EVDOEF + 7,
						validateOnBlur: false,
						width: 350,
						xtype: 'swdopdispdiagtypecombo'
					}, {
						allowBlank: false,
						enableKeyEvents: true,
						fieldLabel: lang['stadiya_zabolevaniya'],
						id: 'evdoewDeseaseStageCombo',
						name: 'DeseaseStage_id',
						tabIndex: TABINDEX_EVDOEF + 8,
						validateOnBlur: false,
						width: 350,
						xtype: 'swdeseasestagecombo'
					}, {
						allowBlank: false,
						enableKeyEvents: true,
						fieldLabel: lang['gruppa_zdorovya'],
						id: 'evdoewHealthKindCombo',
						listeners: {
							'render': function(combo) {
								combo.getStore().load();
							}
						},
						name: 'HealthKind_id',
						tabIndex: TABINDEX_EVDOEF + 9,
						validateOnBlur: false,
						width: 350,
						xtype: 'swhealthkindcombo'
					}, {
						allowBlank: false,
						fieldLabel: lang['na_san-kur_lechenie'],
						hiddenName: 'EvnVizitDispOrp_IsSanKur',
						id: 'evdoewEvnVizitDispOrp_IsSanKurCombo',
						tabIndex: TABINDEX_EVDOEF+10,
						width: 200,
						xtype: 'swyesnocombo'
					}, {
						allowBlank: false,
						fieldLabel: lang['vyiezdnoe'],
						hiddenName: 'EvnVizitDispOrp_IsOut',
						id: 'evdoewEvnVizitDispOrp_IsOutCombo',
						tabIndex: TABINDEX_EVDOEF+11,
						width: 200,
						xtype: 'swyesnocombo'
					}],
					layout: 'form',
					reader: new Ext.data.JsonReader({
						success: function() { }
					}, [
						{ name: 'EvnVizitDispOrp_id' }
					]),
					region: 'center'
				})
			]
        });	
    	sw.Promed.swEvnVizitDispOrpEditWindow.superclass.initComponent.apply(this, arguments);
		this.findById('evdoewDiagCombo').onChange = function( combo, newValue ) {
			if ( newValue > 0 )
			{
				var record = combo.getStore().getById(newValue);
				if ( record )
				{
					diag_code = record.get('Diag_Code').substr(0, 3);
					if ( diag_code.inlist( Array('Z00', 'Z01', 'Z02', 'Z04', 'Z10') ) )
					{
						Ext.getCmp('evdoewDopDispDiagTypeCombo').clearValue();
						Ext.getCmp('evdoewDopDispDiagTypeCombo').disable();
						Ext.getCmp('evdoewDeseaseStageCombo').clearValue();
						Ext.getCmp('evdoewDeseaseStageCombo').disable();
						Ext.getCmp('evdoewHealthKindCombo').getStore().clearFilter();
						Ext.getCmp('evdoewHealthKindCombo').setValue(1);
						Ext.getCmp('evdoewHealthKindCombo').disable();
						return true;
					}
				}
			}
			// если не обработалось, то открывем поля на редактирование
			Ext.getCmp('evdoewDopDispDiagTypeCombo').enable();
			Ext.getCmp('evdoewDeseaseStageCombo').enable();
			Ext.getCmp('evdoewHealthKindCombo').enable();
			Ext.getCmp('evdoewHealthKindCombo').lastQuery = '';
			Ext.getCmp('evdoewHealthKindCombo').getStore().clearFilter();
			Ext.getCmp('evdoewHealthKindCombo').getStore().filterBy(function(record) {
				if ( record.data.HealthKind_id > 1 )
					return true;
			});
			if ( Ext.getCmp('evdoewHealthKindCombo').getValue() == 1 )
				Ext.getCmp('evdoewHealthKindCombo').clearValue();
		};
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

        	var current_window = Ext.getCmp('EvnVizitDispOrpEditWindow');

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
	selectSpecialist: function ()
	{
		// проверяем сан. кур. лечение
		var frm = this.findById('EvnVizitDispOrpEditForm').getForm();
		var OrpDispSpec_id = frm.findField('OrpDispSpec_id').getValue();
		var DopDispAlien_id = frm.findField('DopDispAlien_id').getValue();
		if ( OrpDispSpec_id == 1 && this.EvnVizitDispOrp_IsSanKur_Test )
		{
			frm.findField('EvnVizitDispOrp_IsSanKur').setValue(2);
			frm.findField('EvnVizitDispOrp_IsSanKur').disable();
		}
		else
		{
			frm.findField('EvnVizitDispOrp_IsSanKur').enable();
		}
		// устанавливаем максимальную группу здоровья
		if ( OrpDispSpec_id == 1 && this.Max_HealthKind_id != -1 )
		{
			var max_hk_id = this.Max_HealthKind_id;
			// фильтруем
			frm.findField('HealthKind_id').lastQuery = '';
			frm.findField('HealthKind_id').getStore().clearFilter();
			frm.findField('HealthKind_id').getStore().filterBy(function(record) {
				if ( record.data.HealthKind_id >= max_hk_id )
					return true;
			});
			if ( frm.findField('HealthKind_id').getValue() <= this.Max_HealthKind_id )
				frm.findField('HealthKind_id').setValue(this.Max_HealthKind_id);
		}
		// снимаем фильтр
		else
		{
			frm.findField('HealthKind_id').getStore().clearFilter();
		}
		
		// фильтруем диагнозы
		if ( OrpDispSpec_id == 1 && this.Not_Z_Group_Diag == true )
		{
			this.findById('evdoewDiagCombo').additQueryFilter = "substr(Diag_Code, 1, 3) not in ('Z00', 'Z01', 'Z02', 'Z04', 'Z10')";
			this.findById('evdoewDiagCombo').additClauseFilter = '!(record["Diag_Code"].substring(0,3).inlist(["Z00", "Z01", "Z02", "Z04", "Z10"]))';
		}
		// снимаем фильтр
		else
		{
			this.findById('evdoewDiagCombo').additQueryFilter = false;
			this.findById('evdoewDiagCombo').additClauseFilter = false;
		}

		if ( OrpDispSpec_id > 0 )
		{
			var lpu_section_id = frm.findField('LpuSection_id').getValue();
			var med_staff_fact_id = frm.findField('MedStaffFact_id').getValue();

			frm.findField('LpuSection_id').clearValue();
			frm.findField('MedStaffFact_id').clearValue();
			
			var set_date = Ext.getCmp('evdoewEvnVizitDispOrp_setDate').getValue();
			
			var params = {
				isPolkaAndStom: true
			}
			params.isAliens = (DopDispAlien_id == 2);
			
			if ( OrpDispSpec_id )
			{
				switch ( OrpDispSpec_id )
				{	
					case 1:
						params.arrayLpuSectionProfile = ['0900'];
					break;
					case 2:
						params.arrayLpuSectionProfile = ['2800'];
					break;
					case 3:
						params.arrayLpuSectionProfile = ['2700'];
					break;
					case 4:
						params.arrayLpuSectionProfile = ['2350'];
					break;
					case 5:
						params.arrayLpuSectionProfile = ['2600'];
					break;
					case 6:
						params.arrayLpuSectionProfile = ['2517'];
					break;
					case 7:
						params.arrayLpuSectionProfile = ['1830', '1800'];
					break;
					case 8:
						params.arrayLpuSectionProfile = ['1450'];
					break;
					case 9:
						params.arrayLpuSectionProfile = ['3710'];
					break;
					case 10:
						params.arrayLpuSectionProfile = ['1530','1500','2350'];
					break;
					case 11:
						params.arrayLpuSectionProfile = ['0530', '0510'];
					break;
				}
				
				if ( set_date )
				{
					params.onDate = Ext.util.Format.date(set_date, 'd.m.Y');
					params.onDate = Ext.util.Format.date(set_date, 'd.m.Y');
				}
			}
			
			setLpuSectionGlobalStoreFilter(params);
			setMedStaffFactGlobalStoreFilter(params);

			frm.findField('LpuSection_id').getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));
			frm.findField('MedStaffFact_id').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));

			if ( frm.findField('LpuSection_id').getStore().getById(lpu_section_id) ) {
				frm.findField('LpuSection_id').setValue(lpu_section_id);
			}
		
			if ( frm.findField('MedStaffFact_id').getStore().getById(med_staff_fact_id) ) {
				frm.findField('MedStaffFact_id').setValue(med_staff_fact_id);
			}
		}
		
		if (DopDispAlien_id == 3) { //если предыдущий медосмотр, блокируем поле врач и делаем его необязательным.
			frm.findField('MedStaffFact_id').allowBlank = true;
			frm.findField('MedStaffFact_id').disable();
		} else {
			frm.findField('MedStaffFact_id').allowBlank = false;
			if (frm.findField('MedStaffFact_id').disabled) {
				frm.findField('MedStaffFact_id').disable();
			} else {
				frm.findField('MedStaffFact_id').enable();
			}
		}
	},
	setOrpDispSpecFilter: function() {
		var set_date = this.findById('evdoewEvnVizitDispOrp_setDate').getValue();
		if ( !set_date || set_date == '' )
			var age = this.age;
		else
		{			
			var birth_date = this.Person_BirthDay;
			var age = (birth_date.getMonthsBetween(set_date) - (birth_date.getMonthsBetween(set_date) % 12)) / 12;			
		}
		var sex_id = this.sex_id;
		var UsedOrpDispSpec = this.UsedOrpDispSpec;
		// фильтруем сторе у специальности врача
		Ext.getCmp('evdoewOrpDispSpecCombo').getStore().clearFilter();
		Ext.getCmp('evdoewOrpDispSpecCombo').lastQuery = '';
			
		// 5. В осмотре сделать не доступным выбор "Специальность врача" = "Терапевт", если отсутствует хотя бы один осмотр остальных специалистов (осмотр "8. Дополнительная консультация" в контроле не участвует) или отсутствует хотя бы одно лабораторное исследование (исследование "18. Дополнительное исследование" в контроле не участвует).
		//var sex_id = arguments[0].Sex_id;
		//var age = arguments[0].Person_Age;
		//var UsedOrpDispSpec = arguments[0].formParams['UsedOrpDispSpec'];
		var stand = 0;
		for ( var i = 0; i < UsedOrpDispSpec.length; i++ )
		{
			if ( i.inlist([2,3,4,5,7,8]) && UsedOrpDispSpec[i] == 1 )
				stand++;
		}
		var prov = 6;
		var spec_is_full = ( stand == prov );

		if ( age >= 5 && sex_id == 1 && UsedOrpDispSpec[10] != 1 )
			spec_is_full = false;
			
		if ( sex_id == 2 && UsedOrpDispSpec[6] != 1 )
			spec_is_full = false;
			
		if ( age >= 3 && UsedOrpDispSpec[9] != 1 )
			spec_is_full = false;

		if ( age >= 5 && UsedOrpDispSpec[11] != 1 )
			spec_is_full = false;

		Ext.getCmp('evdoewOrpDispSpecCombo').getStore().filterBy(function(record) 
		{
			if ( record.get('OrpDispSpec_Code') == 1 && (!spec_is_full) )
				return false;
			if ( UsedOrpDispSpec[record.data.OrpDispSpec_id] == 1 )
				return false;
			if ( record.get('OrpDispSpec_Code') == 6 && sex_id != 2 )
				return false;
			if ( record.get('OrpDispSpec_Code') == 10 && sex_id != 1 )
				return false;
			if ( age < 3 && ( record.get('OrpDispSpec_Code').inlist([9, 10, 11]) ) )
				return false;
			if ( age < 5 && ( record.get('OrpDispSpec_Code').inlist([10, 11]) ) )
				return false;
			else
				return true;
		});
	},
    show: function() {
		sw.Promed.swEvnVizitDispOrpEditWindow.superclass.show.apply(this, arguments);

		var current_window = this;

		current_window.restore();
		current_window.center();

        var form = current_window.findById('EvnVizitDispOrpEditForm');
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
/*
		var lpu_section_combo = this.findById('evdoewLpuSectionCombo');
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
*/
/*
		var med_personal_combo = this.findById('evdoewMedPersonalCombo');
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
		}
*/
        if (arguments[0].action)
        {
        	current_window.action = arguments[0].action;
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
		
		if (arguments[0].max_date)
        {
        	current_window.max_date = arguments[0].max_date;
        }
				
		if ( arguments[0].Year ) {
			this.Year = arguments[0].Year;
		}
		else 
			this.Year = null; //Date.parseDate(getGlobalOptions().date, 'd.m.Y').getFullYear();
		
		current_window.EvnVizitDispOrp_IsSanKur_Test = false;
		if ( arguments[0].formParams.EvnVizitDispOrp_IsSanKur_Test )
        {
        	current_window.EvnVizitDispOrp_IsSanKur_Test = arguments[0].formParams.EvnVizitDispOrp_IsSanKur_Test;
        }
		
		current_window.Max_HealthKind_id = -1;
		if ( arguments[0].formParams.Max_HealthKind_id )
        {
        	current_window.Max_HealthKind_id = arguments[0].formParams.Max_HealthKind_id;
        }
		
		current_window.Not_Z_Group_Diag = false;
		if ( arguments[0].formParams.Not_Z_Group_Diag )
        {
        	current_window.Not_Z_Group_Diag = arguments[0].formParams.Not_Z_Group_Diag;			
        }
		
		current_window.findById('evdoewPersonInformationFrame').load({
			Person_id: (arguments[0].Person_id ? arguments[0].Person_id : ''),
			Person_Birthday: (arguments[0].Person_Birthday ? arguments[0].Person_Birthday : ''),
			Person_Firname: (arguments[0].Person_Firname ? arguments[0].Person_Firname : ''),
			Person_Secname: (arguments[0].Person_Secname ? arguments[0].Person_Secname : ''),
			Person_Surname: (arguments[0].Person_Surname ? arguments[0].Person_Surname : ''),
			callback: function() {
				var field = base_form.findField('EvnVizitDispOrp_setDate');
				clearDateAfterPersonDeath('personpanelid', 'evdoewPersonInformationFrame', field);
			}
		});
		
		
		
  		var loadMask = new Ext.LoadMask(Ext.get('EvnVizitDispOrpEditWindow'), { msg: LOAD_WAIT });
		loadMask.show();

        // чистим фильтр у группы здоровья
		this.findById('EvnVizitDispOrpEditForm').getForm().findField('HealthKind_id').getStore().clearFilter();

		this.sex_id = arguments[0].Sex_id;
		this.age = arguments[0].Person_Age;		
		this.UsedOrpDispSpec = arguments[0].formParams['UsedOrpDispSpec'];
		this.Person_BirthDay = arguments[0].Person_Birthday;
		current_window.findById('evdoewEvnVizitDispOrp_setDate').setValue(arguments[0].formParams['EvnVizitDispOrp_setDate']);
		this.setOrpDispSpecFilter();
		
		form.getForm().setValues(arguments[0].formParams);
		
		var med_personal_id = arguments[0].formParams.MedPersonal_id;
		var lpu_section_id = arguments[0].formParams.LpuSection_id;
		
		form.getForm().clearInvalid();
		
		switch (current_window.action)
        {
            case 'add':
                current_window.setTitle(WND_POL_EVDDADD);
                current_window.enableEdit(true);
				var sex_id = arguments[0].Sex_id;								
				loadMask.hide();
				current_window.findById('evdoewEvnVizitDispOrp_setDate').focus(false, 250);
				Ext.getCmp('evdoewHealthKindCombo').lastQuery = '';
				Ext.getCmp('evdoewHealthKindCombo').getStore().clearFilter();
				Ext.getCmp('evdoewHealthKindCombo').getStore().filterBy(function(record) {
					if ( record.data.HealthKind_id > 1 )
						return true;
				});
				if ( Ext.getCmp('evdoewHealthKindCombo').getValue() == 1 )
					Ext.getCmp('evdoewHealthKindCombo').clearValue();
				var diag_combo = this.findById('evdoewDiagCombo');
				var diag_id = 10880; // Z00.0
				diag_combo.getStore().load({
					callback: function() {
						diag_combo.getStore().each(function(record) {
							if (record.data.Diag_id == diag_id)
							{
								diag_combo.fireEvent('select', diag_combo, record, 0);
								diag_combo.onChange( diag_combo, diag_id );
								diag_combo.disable();
								diag_combo.enable();
							}
						});
					},
					params: { where: "where DiagLevel_id = 4 and Diag_id = " + diag_id }
				});
				// ограничиваем годом пришедшим извне.
				this.setMinDate(current_window.action);
				
				current_window.findById('evdoewEvnVizitDispOrp_IsSanKurCombo').setValue(1);
				current_window.findById('evdoewEvnVizitDispOrp_IsOutCombo').setValue(1);
				Ext.getCmp('evdoewDopDispAlien_idCombo').setValue(1);
                break;

        	case 'edit':
        	    current_window.setTitle(WND_POL_EVDDEDIT);
                current_window.enableEdit(true);

				var diag_combo = this.findById('evdoewDiagCombo');
				var diag_id = diag_combo.getValue();
				if (diag_id != null && diag_id.toString().length > 0)
				{
					diag_combo.getStore().load({
						callback: function() {
							diag_combo.getStore().each(function(record) {
								if (record.data.Diag_id == diag_id)
								{
									diag_combo.fireEvent('select', diag_combo, record, 0);
								}
							});
						},
						params: { where: "where DiagLevel_id = 4 and Diag_id = " + diag_id }
					});
					var record = diag_combo.getStore().getById(diag_id);
					if ( record )
					{
						diag_code = record.get('Diag_Code').substr(0, 3);
						if ( diag_code.inlist( Array('Z00', 'Z01', 'Z02', 'Z04', 'Z10') ) )
						{
							Ext.getCmp('evdoewDopDispDiagTypeCombo').clearValue();
							Ext.getCmp('evdoewDopDispDiagTypeCombo').disable();
							Ext.getCmp('evdoewDeseaseStageCombo').clearValue();
							Ext.getCmp('evdoewDeseaseStageCombo').disable();
							Ext.getCmp('evdoewHealthKindCombo').getStore().clearFilter();
							Ext.getCmp('evdoewHealthKindCombo').setValue(1);
							Ext.getCmp('evdoewHealthKindCombo').disable();
						}
					}
				}				
				Ext.getCmp('evdoewOrpDispSpecCombo').fireEvent('change', Ext.getCmp('evdoewOrpDispSpecCombo'), Ext.getCmp('evdoewOrpDispSpecCombo').getValue() );
				// устанавливаем врача
				form.findById('evdoewMedPersonalCombo').getStore().findBy(function(record) {
					if ( record.get('MedPersonal_id') == med_personal_id )
					{
						form.findById('evdoewMedPersonalCombo').setValue(record.get('MedStaffFact_id'));
						return true;
					}
				});		
				
				this.setMinDate(current_window.action);
				loadMask.hide();
				
				current_window.findById('evdoewEvnVizitDispOrp_setDate').focus(false, 250);
				this.findById('evdoewLpuSectionCombo').setValue(lpu_section_id);
                break;

            case 'view':
                current_window.setTitle(WND_POL_EVDDVIEW);
                current_window.enableEdit(false);

				var diag_combo = this.findById('evdoewDiagCombo');
				var diag_id = diag_combo.getValue();
				if (diag_id != null && diag_id.toString().length > 0)
				{
					diag_combo.getStore().load({
						callback: function() {
							diag_combo.getStore().each(function(record) {
								if (record.data.Diag_id == diag_id)
								{
									diag_combo.fireEvent('select', diag_combo, record, 0);
								}
							});
						},
						params: { where: "where DiagLevel_id = 4 and Diag_id = " + diag_id }
					});
				}
				Ext.getCmp('evdoewOrpDispSpecCombo').fireEvent('change', Ext.getCmp('evdoewOrpDispSpecCombo'), Ext.getCmp('evdoewOrpDispSpecCombo').getValue() );
				// устанавливаем врача
				form.findById('evdoewMedPersonalCombo').getStore().findBy(function(record) {
					if ( record.get('MedPersonal_id') == med_personal_id )
					{
						form.findById('evdoewMedPersonalCombo').setValue(record.get('MedStaffFact_id'));
						return true;
					}
				});
				//this.setMinDate(current_window.action);
				loadMask.hide();
				current_window.buttons[1].focus();
				this.findById('evdoewLpuSectionCombo').setValue(lpu_section_id);
                break;
        }

        form.getForm().clearInvalid();
	},
	setMinDate: function(action)
	{
		if (action=='add')
		{
			// ограничиваем годом пришедшим извне.
			if (this.Year && this.Year>0)
			{
				this.findById('evdoewEvnVizitDispOrp_setDate').setMinValue(Date.parseDate('01.01.' + this.Year, 'd.m.Y'));
			}
			else 
			{
				this.findById('evdoewEvnVizitDispOrp_setDate').setMinValue(Date.parseDate('01.01.' + (Date.parseDate(getGlobalOptions().date, 'd.m.Y').getFullYear()-1), 'd.m.Y'));
			}
		}
		else 
		{
			var year = this.findById('evdoewEvnVizitDispOrp_setDate').getValue().getFullYear();
			if (year)
			{
				this.findById('evdoewEvnVizitDispOrp_setDate').setMinValue(Date.parseDate('01.01.' + year, 'd.m.Y'));
			}
		}
	},
	width: 700
});