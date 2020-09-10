/**
* swEvnVizitDispDopEditWindow - окно редактирования/добавления осмотра по доп. диспансеризации
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package		Polka
* @access		public
* @copyright		Copyright (c) 2009 Swan Ltd.
* @author		Ivan Petukhov aka Lich (megatherion@list.ru)
* @originalauthor	Stas Bykov aka Savage (savage1981@gmail.com)
* @version		29.06.2009
* @comment		Префикс для id компонентов EVDDEW (swEvnVizitDispDopEditWindow)
*				tabIndex: 2701
*
*
* Использует: окно редактирования талона по доп. диспансеризации (swEvnPLDispDopEditWindow)
*/
/*NO PARSE JSON*/
sw.Promed.swEvnVizitDispDopEditWindow = Ext.extend(sw.Promed.BaseForm, {
	action: null,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	collapsible: false,
	/* */
	codeRefresh: true,
	objectName: 'swEvnVizitDispDopEditWindow',
	objectSrc: '/jscore/Forms/Polka/swEvnVizitDispDopEditWindow.js',
	
	doSave: function() {
		var add_flag = true;

		var index = -1;
		var dopdisp_spec_id = this.findById('EVDDEW_DopDispSpecCombo').getValue();
		var dopdisp_spec_name = '';
		var lpu_section_id = this.findById('EVDDEW_LpuSectionCombo').getValue();
		var lpu_section_name = '';
		var med_staff_fact_id = this.findById('EVDDEW_MedPersonalCombo').getValue();

		this.findById('EVDDEW_DiagCombo').fireEvent('blur', this.findById('EVDDEW_DiagCombo'));

		var diag_id = this.findById('EVDDEW_DiagCombo').getValue();
		var diag_code = '';
		var dopdispdiagtype_id = this.findById('EVDDEW_DopDispDiagTypeCombo').getValue();
		var deseasestage_id = this.findById('EVDDEW_DeseaseStageCombo').getValue();
		var healthkind_id = this.findById('EVDDEW_HealthKindCombo').getValue();
		var issankur_id = this.findById('EVDDEW_EvnVizitDispDop_IsSanKurCombo').getValue();
		var isout_id = this.findById('EVDDEW_EvnVizitDispDop_IsOutCombo').getValue();
		var isalien_id = Ext.getCmp('EVDDEW_DopDispAlien_idCombo').getValue();
		var recommendations = this.findById('EVDDEW_EvnVizitDispDop_Recommendations').getValue();

		var record_status = this.findById('EVDDEW_Record_Status').getValue();

		if ( !this.findById('EvnVizitDispDopEditForm').getForm().isValid() )
		{
			Ext.MessageBox.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					this.findById('EvnVizitDispDopEditForm').getFirstInvalidEl().focus(true);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
		
		if ( dopdisp_spec_id == 1 && (this.findById('EVDDEW_EvnVizitDispDop_setDate').getValue() < this.max_date) )
		{
			Ext.MessageBox.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					this.findById('EVDDEW_EvnVizitDispDop_setDate').focus(false);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: "Осмотр терапевта не может быть проведен ранее других осмотров или даты получения результатов исследований.",
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		var set_date = this.findById('EVDDEW_EvnVizitDispDop_setDate').getValue();
		if ( set_date < Date.parseDate('01.01.' + set_date.getFullYear(), 'd.m.Y') )
		{
			Ext.MessageBox.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					this.findById('EVDDEW_EvnVizitDispDop_setDate').focus(false);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: lang['data_nachala_ne_mojet_byit_menshe_01_01'] + set_date.getFullYear() + '.',
				title: lang['oshibka']
			});
			return false;
		}

		if ( record_status == 1 )
		{
			record_status = 2;
		}

		index = this.findById('EVDDEW_DopDispSpecCombo').getStore().findBy(function(rec) { return rec.get('DopDispSpec_id') == dopdisp_spec_id; });
		if (index >= 0)
		{
			dopdisp_spec_name = this.findById('EVDDEW_DopDispSpecCombo').getStore().getAt(index).data.DopDispSpec_Name;
		}
		
		index = this.findById('EVDDEW_LpuSectionCombo').getStore().findBy(function(rec) { return rec.get('LpuSection_id') == lpu_section_id; });
		if (index >= 0)
		{
			lpu_section_name = this.findById('EVDDEW_LpuSectionCombo').getStore().getAt(index).data.LpuSection_Name;
		}

		
		record = this.findById('EVDDEW_MedPersonalCombo').getStore().getById(med_staff_fact_id);
		if ( record ) {
			med_personal_fio = record.get('MedPersonal_Fio');
			med_personal_id = record.get('MedPersonal_id');
		} else {
			med_personal_fio = null;
			med_personal_id = null;
		}				

		index = this.findById('EVDDEW_DiagCombo').getStore().findBy(function(rec) { return rec.get('Diag_id') == diag_id; });
		if (index >= 0)
		{
			diag_code = this.findById('EVDDEW_DiagCombo').getStore().getAt(index).data.Diag_Code;
		}
		
		if ( this.action != 'add' )
		{
			add_flag = false;
		}

		var data = [{
			'EvnVizitDispDop_id': this.findById('EVDDEW_EvnVizitDispDop_id').getValue(),
			'DopDispSpec_id': dopdisp_spec_id,
			'LpuSection_id': lpu_section_id,
			'MedPersonal_id': med_personal_id,
			'Diag_id': diag_id,
			'DopDispSpec_Name': dopdisp_spec_name,
			'EvnVizitDispDop_setDate': this.findById('EVDDEW_EvnVizitDispDop_setDate').getValue(),
			'LpuSection_Name': lpu_section_name,
			'MedPersonal_Fio': med_personal_fio,
			'Diag_Code': diag_code,
			'DopDispDiagType_id': dopdispdiagtype_id,
			'DeseaseStage_id': deseasestage_id,
			'HealthKind_id': healthkind_id,
			'EvnVizitDispDop_IsSanKur': issankur_id,
			'EvnVizitDispDop_IsOut': isout_id,
			'DopDispAlien_id': isalien_id,
			'EvnVizitDispDop_Recommendations': recommendations,
			'Record_Status': record_status
		}];

		this.callback(data, add_flag);
		this.hide();
	},
	draggable: true,
	enableEdit: function(enable) {
		if (enable)
		{
			this.findById('EVDDEW_EvnVizitDispDop_setDate').enable();
			this.findById('EVDDEW_LpuSectionCombo').enable();
			this.findById('EVDDEW_DopDispSpecCombo').enable();
			this.findById('EVDDEW_MedPersonalCombo').enable();
			this.findById('EVDDEW_DiagCombo').enable();
			this.findById('EVDDEW_DopDispDiagTypeCombo').enable();
			this.findById('EVDDEW_DeseaseStageCombo').enable();
			this.findById('EVDDEW_HealthKindCombo').enable();
			this.findById('EVDDEW_EvnVizitDispDop_IsSanKurCombo').enable();
			this.findById('EVDDEW_EvnVizitDispDop_IsOutCombo').enable();
			this.findById('EVDDEW_DopDispAlien_idCombo').enable();
			this.findById('EVDDEW_EvnVizitDispDop_Recommendations').enable();

			// enable() для кнопок на гридах

			this.buttons[0].show();
		}
		else
		{
			this.findById('EVDDEW_EvnVizitDispDop_setDate').disable();
			this.findById('EVDDEW_LpuSectionCombo').disable();
			this.findById('EVDDEW_DopDispSpecCombo').disable();
			this.findById('EVDDEW_MedPersonalCombo').disable();
			this.findById('EVDDEW_DiagCombo').disable();
			this.findById('EVDDEW_DopDispDiagTypeCombo').disable();
			this.findById('EVDDEW_DeseaseStageCombo').disable();
			this.findById('EVDDEW_HealthKindCombo').disable();
			this.findById('EVDDEW_EvnVizitDispDop_IsSanKurCombo').disable();
			this.findById('EVDDEW_EvnVizitDispDop_IsOutCombo').disable();
			this.findById('EVDDEW_DopDispAlien_idCombo').disable();
			this.findById('EVDDEW_EvnVizitDispDop_Recommendations').disable();

			// disable() для кнопок на гридах

			this.buttons[0].hide();
		}
	},
	height: 455,
	id: 'EvnVizitDispDopEditWindow',
	initComponent: function() {
		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.doSave();
				}.createDelegate(this),
				iconCls: 'save16',
				id: 'EVDDEW_SaveButton',
				tabIndex: 2712,
				text: BTN_FRMSAVE
			}, {
				text: '-'
			},
				HelpButton(this),
			{
				handler: function() {
					this.hide();
				}.createDelegate(this),
				iconCls: 'cancel16',
				id: 'EVDDEW_CancelButton',
				onTabAction: function() {
					this.findById('EVDDEW_EvnVizitDispDop_setDate').focus(true, 200);
				}.createDelegate(this),
				onShiftTabAction: function() {
					this.findById('EVDDEW_SaveButton').focus(true, 200);
				}.createDelegate(this),
				tabIndex: 2713,
				text: BTN_FRMCANCEL
			}],
			items: [
				new sw.Promed.PersonInformationPanelShort({
					id: 'EVDDEW_PersonInformationFrame',
					region: 'north'
				}),
				new Ext.form.FormPanel({
					bodyBorder: false,
					bodyStyle: 'padding: 5px 5px 0',
					border: false,
					frame: false,
					id: 'EvnVizitDispDopEditForm',
					labelAlign: 'right',
					labelWidth: 150,
					items: [{
						id: 'EVDDEW_EvnVizitDispDop_id',
						name: 'EvnVizitDispDop_id',
						value: 0,
						xtype: 'hidden'
					}, {
						name: 'accessType',
						value: '',
						xtype: 'hidden'
					}, {
						name: 'MedPersonal_id',
						value: 0,
						xtype: 'hidden'
					}, {
						id: 'EVDDEW_Record_Status',
						name: 'Record_Status',
						value: 0,
						xtype: 'hidden'
					}, {
						allowBlank: false,
						enableKeyEvents: true,
						fieldLabel: lang['data_osmotra'],
						format: 'd.m.Y',
						id: 'EVDDEW_EvnVizitDispDop_setDate',
						listeners: {
							'keydown':  function(inp, e) {
								if ( e.shiftKey && e.getKey() == Ext.EventObject.TAB )
								{
									e.stopEvent();
									Ext.getCmp('EVDDEW_CancelButton').focus(true, 200);
								}
							},
							'change': function(field, newValue, oldValue) {
								if (blockedDateAfterPersonDeath('personpanelid', 'EVDDEW_PersonInformationFrame', field, newValue, oldValue)) return;
								
								this.setDopDispSpecFilter();
								this.selectSpecialist();
								/*
								if ( newValue )
								{
									
									var base_form = this.findById('EvnVizitDispDopEditForm').getForm();

									var lpu_section_id = base_form.findField('LpuSection_id').getValue();
									var med_staff_fact_id = base_form.findField('MedStaffFact_id').getValue();
									
									var spec_id = base_form.findField('DopDispSpec_id').getValue();

									base_form.findField('LpuSection_id').clearValue();
									base_form.findField('MedStaffFact_id').clearValue();

									var params = {
										isPolka: true
									}
									
									if ( newValue )
									{
										if ( spec_id > 0 )
										{
											switch ( spec_id )
											{
												case 1:
													params.arrayLpuSectionProfile = ['1000', '1003'];
												break;
												case 2:
													params.arrayLpuSectionProfile = ['2517'];
												break;
												case 3:
													params.arrayLpuSectionProfile = ['2800'];
												break;
												case 5:
													params.arrayLpuSectionProfile = ['2300'];
												break;
												case 6:
													params.arrayLpuSectionProfile = ['2700'];
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
						name: 'EvnVizitDispDop_setDate',
						plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
						tabIndex: 2701,
						width: 100,
						xtype: 'swdatefield'
					}, {
						allowBlank: false,
						enableKeyEvents: true,
						fieldLabel: lang['spetsialnost_vracha'],
						hiddenName: 'DopDispSpec_id',
						id: 'EVDDEW_DopDispSpecCombo',
						listeners: {
							'change': function(combo, newValue) {
								this.selectSpecialist();
							}.createDelegate(this)
						},
						tabIndex: 2702,
						validateOnBlur: false,
						width: 350,
						xtype: 'swdopdispspeccombo'
					},{
						allowBlank: false,
						fieldLabel: lang['storonniy_spetsialist'],						
						id: 'EVDDEW_DopDispAlien_idCombo',
						comboSubject: 'DopDispAlien',
						sortField: 'DopDispAlien_Code',
						tabIndex: 2703,
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
						id: 'EVDDEW_LpuSectionCombo',
						lastQuery: '',
						listWidth: 650,
						linkedElements: [
							'EVDDEW_MedPersonalCombo'
						],
						tabIndex: 2704,
						width: 450,
						xtype: 'swlpusectionglobalcombo'
					}, {
						allowBlank: false,
						hiddenName: 'MedStaffFact_id',
						id: 'EVDDEW_MedPersonalCombo',
						lastQuery: '',
						listWidth: 650,
						parentElementId: 'EVDDEW_LpuSectionCombo',
						tabIndex: 2705,
						width: 450,
						xtype: 'swmedstafffactglobalcombo'
					}, {
						allowBlank: false,
						hiddenName: 'Diag_id',
						id: 'EVDDEW_DiagCombo',
						listWidth: 580,
						tabIndex: 2706,
						width: 450,
						xtype: 'swdiagcombo'
					}, {
						allowBlank: false,
						enableKeyEvents: true,
						fieldLabel: lang['zabolevanie'],
						hiddenName: 'DopDispDiagType_id',
						id: 'EVDDEW_DopDispDiagTypeCombo',
						tabIndex: 2707,
						validateOnBlur: false,
						width: 350,
						xtype: 'swdopdispdiagtypecombo'
					}, {
						allowBlank: false,
						enableKeyEvents: true,
						fieldLabel: lang['stadiya_zabolevaniya'],
						id: 'EVDDEW_DeseaseStageCombo',
						name: 'DeseaseStage_id',
						tabIndex: 2708,
						validateOnBlur: false,
						width: 350,
						xtype: 'swdeseasestagecombo'
					}, {
						allowBlank: false,
						enableKeyEvents: true,
						fieldLabel: lang['gruppa_zdorovya'],
						hiddenName: 'HealthKind_id',
						id: 'EVDDEW_HealthKindCombo',
						lastQuery: '',
						listeners: {
							'render': function(combo) {
								combo.getStore().load();
							}
						},
						tabIndex: 2709,
						validateOnBlur: false,
						width: 350,
						xtype: 'swhealthkindcombo'
					}, {
						allowBlank: false,
						fieldLabel: lang['na_san-kur_lechenie'],
						hiddenName: 'EvnVizitDispDop_IsSanKur',
						id: 'EVDDEW_EvnVizitDispDop_IsSanKurCombo',
						tabIndex: 2710,
						width: 200,
						xtype: 'swyesnocombo'
					}, {
						allowBlank: false,
						fieldLabel: lang['vyiezdnoe'],
						hiddenName: 'EvnVizitDispDop_IsOut',
						id: 'EVDDEW_EvnVizitDispDop_IsOutCombo',
						tabIndex: 2711,
						width: 200,
						xtype: 'swyesnocombo'
					}, {
						allowBlank: true,
						fieldLabel: lang['rekomendatsii'],
						hiddenName: 'EvnVizitDispDop_Recommendations',
						name: 'EvnVizitDispDop_Recommendations',
						id: 'EVDDEW_EvnVizitDispDop_Recommendations',						
						tabIndex: 2712,
						width: 450,
						xtype: 'textarea'
					}],
					layout: 'form',
					reader: new Ext.data.JsonReader({
						success: function() { }
					}, [
						{ name: 'EvnVizitDispDop_id' },
						{ name: 'Record_Status' },
						{ name: 'EvnVizitDispDop_setDate' },
						{ name: 'DopDispSpec_id' },
						{ name: 'accessType' },
						{ name: 'DopDispAlien_id' },
						{ name: 'LpuSection_id' },
						{ name: 'MedPersonal_id'},
						{ name: 'Diag_id' },
						{ name: 'DopDispDiagType_id' },
						{ name: 'DeseaseStage_id' },
						{ name: 'HealthKind_id' },
						{ name: 'EvnVizitDispDop_IsSanKur' },
						{ name: 'EvnVizitDispDop_IsOut' },
						{ name: 'EvnVizitDispDop_Recommendations' }
					]),
					region: 'center'
				})
			]
		});	
		sw.Promed.swEvnVizitDispDopEditWindow.superclass.initComponent.apply(this, arguments);

		this.findById('EVDDEW_DiagCombo').onChange = function(combo, newValue, oldValue) {
			var base_form = this.findById('EvnVizitDispDopEditForm').getForm();

			var desease_stage_combo = base_form.findField('DeseaseStage_id');
			var dop_disp_diag_type_combo = base_form.findField('DopDispDiagType_id');
			var dop_disp_spec_combo = base_form.findField('DopDispSpec_id');
			var health_kind_combo = base_form.findField('HealthKind_id');

			var desease_stage_id = desease_stage_combo.getValue();
			var dop_disp_diag_type_id = dop_disp_diag_type_combo.getValue();
			var dop_disp_spec_id = dop_disp_spec_combo.getValue().toString();
			var health_kind_id = health_kind_combo.getValue();

			health_kind_combo.clearValue();
			health_kind_combo.disable();
			desease_stage_combo.clearValue();
			desease_stage_combo.disable();
			dop_disp_diag_type_combo.clearValue();
			dop_disp_diag_type_combo.disable();

			var record = combo.getStore().getById(newValue);

			if ( !record || !dop_disp_spec_id ) {
				return false;
			}

			diag_code = record.get('Diag_Code').substr(0, 3);

			health_kind_combo.getStore().clearFilter();
			health_kind_combo.enable();

			if ( diag_code.inlist(['Z00', 'Z01', 'Z02', 'Z04', 'Z10']) ) {
				if ( dop_disp_spec_id != '1' ) {
					health_kind_combo.disable();
					health_kind_combo.setValue(1);
				}
				else {
					health_kind_combo.getStore().filterBy(function(rec) {
						if ( Number(rec.get('HealthKind_id')) >= Number(this.Max_HealthKind_id) ) {
							return true;
						}
						else {
							return false;
						}
					}.createDelegate(this));

					if ( health_kind_id >= this.Max_HealthKind_id ) {
						health_kind_combo.setValue(health_kind_id);
					}
				}
			}
			else if ( dop_disp_spec_id != '1' ) {
				desease_stage_combo.enable();
				dop_disp_diag_type_combo.enable();

				desease_stage_combo.setValue(desease_stage_id);
				dop_disp_diag_type_combo.setValue(dop_disp_diag_type_id);

				health_kind_combo.getStore().filterBy(function(rec) {
					if ( Number(rec.get('HealthKind_id')) > 1 ) {
						return true;
					}
					else {
						return false;
					}
				});

				if ( Number(health_kind_id) > 1 ) {
					health_kind_combo.setValue(health_kind_id);
				}
			}
			else {
				desease_stage_combo.enable();
				dop_disp_diag_type_combo.enable();

				desease_stage_combo.setValue(desease_stage_id);
				dop_disp_diag_type_combo.setValue(dop_disp_diag_type_id);

				health_kind_combo.getStore().filterBy(function(rec) {
					if ( Number(rec.get('HealthKind_id')) >= Number(this.Max_HealthKind_id) ) {
						return true;
					}
					else {
						return false;
					}
				}.createDelegate(this));

				if ( Number(health_kind_id) >= Number(this.Max_HealthKind_id) ) {
					health_kind_combo.setValue(health_kind_id);
				}
			}
		}.createDelegate(this);
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

			var current_window = Ext.getCmp('EvnVizitDispDopEditWindow');

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
	maximizable: false,
	minHeight: 370,
	minWidth: 700,
	modal: true,
	onHide: Ext.emptyFn,
	plain: true,
	resizable: false,
	
	selectSpecialist: function ()
	{
		var base_form = this.findById('EvnVizitDispDopEditForm').getForm();
		// проверяем сан. кур. лечение
		var DopDispSpec_id = base_form.findField('DopDispSpec_id').getValue();
		var DopDispAlien_id = base_form.findField('DopDispAlien_id').getValue();
		if ( DopDispSpec_id == 1 && this.EvnVizitDispDop_IsSanKur_Test ) {
			base_form.findField('EvnVizitDispDop_IsSanKur').setValue(2);
			base_form.findField('EvnVizitDispDop_IsSanKur').disable();
		} else if ( this.action != 'view' ) {
			base_form.findField('EvnVizitDispDop_IsSanKur').enable();
		}
		// base_form.findField('Diag_id').additQueryFilter = false;

		base_form.findField('Diag_id').onChange(base_form.findField('Diag_id'), base_form.findField('Diag_id').getValue());

		if ( DopDispSpec_id > 0 )
		{
			var lpu_section_id = base_form.findField('LpuSection_id').getValue();
			var med_staff_fact_id = base_form.findField('MedStaffFact_id').getValue();
			
			base_form.findField('LpuSection_id').clearValue();
			base_form.findField('MedStaffFact_id').clearValue();

			var set_date = base_form.findField('EvnVizitDispDop_setDate').getValue();
			
			var section_filter_params = {
				isPolka: true
			};									
			var medstafffact_filter_params = {
				isPolka: true
			};

			section_filter_params.isAliens = (DopDispAlien_id == 2);
			medstafffact_filter_params.isAliens = (DopDispAlien_id == 2);
			
			if ( DopDispSpec_id )
			{
				switch ( DopDispSpec_id )
				{
					/* Для Уфы, профили отделений описаны в задаче #4246 */
					case 1:
						if ( getRegionNick() == 'ufa' )
						{
							section_filter_params.arrayLpuSectionProfile = ['500', '527', '600', '627', '800', '827'];
							medstafffact_filter_params.arrayLpuSectionProfile = ['500', '527', '600', '627', '800', '827'];
						}
						else if ( getRegionNick() == 'perm' )
						{
							section_filter_params.arrayLpuSectionProfile = ['1000', '1003', '1011'];
							medstafffact_filter_params.arrayLpuSectionProfile = ['1000', '1003', '1011'];
						}
						break;

					case 2:
						if ( getRegionNick() == 'ufa' )
						{
							section_filter_params.arrayLpuSectionProfile = ['522', '622', '822'];
							medstafffact_filter_params.arrayLpuSectionProfile = ['522', '622', '822'];
						}
						else if ( getRegionNick() == 'perm' )
						{
							section_filter_params.arrayLpuSectionProfile = ['2517'];
							medstafffact_filter_params.arrayLpuSectionProfile = ['2517'];
						}
						break;
					case 3:
						if ( getRegionNick() == 'ufa' )
						{
							section_filter_params.arrayLpuSectionProfile = ['509', '609', '809'];
							medstafffact_filter_params.arrayLpuSectionProfile = ['509', '609', '809'];
						}
						else if ( getRegionNick() == 'perm' )
						{
							section_filter_params.arrayLpuSectionProfile = ['2800'];
							medstafffact_filter_params.arrayLpuSectionProfile = ['2800'];
						}
						break;
					case 5:
						if ( getRegionNick() == 'ufa' )
						{
							section_filter_params.arrayLpuSectionProfile = ['510', '610', '810'];
							medstafffact_filter_params.arrayLpuSectionProfile = ['510', '610', '810'];
						}
						else if ( getRegionNick() == 'perm' )
						{
							section_filter_params.arrayLpuSectionProfile = ['2300'];
							medstafffact_filter_params.arrayLpuSectionProfile = ['2300'];
						}
						break;

					case 6:
						if ( getRegionNick() == 'ufa' )
						{
							section_filter_params.arrayLpuSectionProfile = ['518', '618', '818'];
							medstafffact_filter_params.arrayLpuSectionProfile = ['518', '618', '818'];
						}
						else if ( getRegionNick() == 'perm' )
						{
							section_filter_params.arrayLpuSectionProfile = ['2700'];
							medstafffact_filter_params.arrayLpuSectionProfile = ['2700'];
						}
						break;
				}
				
			}
			if ( set_date )
			{
				section_filter_params.onDate = Ext.util.Format.date(set_date, 'd.m.Y');
				section_filter_params.onDate = Ext.util.Format.date(set_date, 'd.m.Y');
			}
			
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
			
						
			if (DopDispAlien_id == 3) { //если предыдущий медосмотр, блокируем поле врач и делаем его необязательным.
				base_form.findField('MedStaffFact_id').allowBlank = true;
				base_form.findField('MedStaffFact_id').disable();
			} else {
				base_form.findField('MedStaffFact_id').allowBlank = false;
				if (base_form.findField('MedStaffFact_id').disabled) {
					base_form.findField('MedStaffFact_id').disable();
				} else {
					base_form.findField('MedStaffFact_id').enable();
				}
			}
		}
	},
	setDopDispSpecFilter: function() {
		var set_date = this.findById('EVDDEW_EvnVizitDispDop_setDate').getValue();
		if ( !set_date || set_date == '' )
			var age = this.age;
		else
		{			
			var birth_date = this.Person_BirthDay;
			var age = (birth_date.getMonthsBetween(set_date) - (birth_date.getMonthsBetween(set_date) % 12)) / 12;			
		}
		var sex_id = this.sex_id;
		var UsedDopDispSpec = this.UsedDopDispSpec ? this.UsedDopDispSpec : [];
		var UsedDopDispUslugaType = this.UsedDopDispUslugaType ? this.UsedDopDispUslugaType : [];
		// 5. В осмотре сделать не доступным выбор "Специальность врача" = "Терапевт", если отсутствует хотя бы один осмотр остальных
		// специалистов (осмотр "8. Дополнительная консультация" в контроле не участвует) или отсутствует хотя бы одно лабораторное
		// исследование (исследование "18. Дополнительное исследование" в контроле не участвует).
		var base_form = this.findById('EvnVizitDispDopEditForm').getForm();
		base_form.findField('DopDispSpec_id').getStore().clearFilter();
		base_form.findField('DopDispSpec_id').lastQuery = '';

		max_count = 13;

		if ( age >= 45 ) {
			max_count++;
		}

		if ( age >= 40 && sex_id == 2 ) {
			max_count++;
		}

		if ( sex_id == 2 ) {
			max_count++;
		}

		var count = 0;

		for ( i = 0; i < UsedDopDispUslugaType.length; i++ ) {
			if ( UsedDopDispUslugaType[i] == 1 && i != 8 ) {
				count++;
			}
		}

		var usluga_is_full = (count >= max_count);

		max_count = 3;

		if ( sex_id == 2 ) {
			max_count++;
		}

		var count = 0;

		for ( i = 0; i < UsedDopDispSpec.length; i++ ) {
			if ( UsedDopDispSpec[i] == 1 && i != 1 ) {
				count++;
			}
		}

		var spec_is_full = (count >= max_count);

		base_form.findField('DopDispSpec_id').getStore().filterBy(function(record) {
			if ( record.get('DopDispSpec_Code').inlist([4, 7, 8]) ) {
				return false;
			}
			else {
				if ( record.get('DopDispSpec_Code') == 1 && (!spec_is_full/*|| !usluga_is_full*/) ) {
					return false;
				}

				if ( UsedDopDispSpec[record.get('DopDispSpec_id')] == 1 ) {
					return false;
				}

				if ( record.get('DopDispSpec_Code') == 2 && sex_id != 2 ) {
					return false;
				}

				return true;
			}
		});
	},
	temparg: new Object(),
	show: function() {
		sw.Promed.swEvnVizitDispDopEditWindow.superclass.show.apply(this, arguments);

		var win = this;

		this.center();

		var base_form = this.findById('EvnVizitDispDopEditForm').getForm();
		base_form.reset();

		this.action = null;
		this.callback = Ext.emptyFn;
		this.EvnVizitDispDop_IsSanKur_Test = false;
		this.max_date = null;
		this.Max_HealthKind_id = -1;
		this.Not_Z_Group_Diag = false;
		this.onHide = Ext.emptyFn;

		if ( !arguments[0] || !arguments[0].formParams ) {
			sw.swMsg.alert(lang['soobschenie'], lang['nevernyie_parametryi'], function() { this.hide(); }.createDelegate(this) );
			return false;
		}
		
		this.temparg = arguments[0];
		
		if ( arguments[0].action ) {
			this.action = arguments[0].action;
		}

		if ( arguments[0].callback ) {
			this.callback = arguments[0].callback;
		}

		if ( arguments[0].formParams.EvnVizitDispDop_IsSanKur_Test ) {
			this.EvnVizitDispDop_IsSanKur_Test = arguments[0].formParams.EvnVizitDispDop_IsSanKur_Test;
		}

		if ( arguments[0].max_date ) {
			this.max_date = arguments[0].max_date;
		}

		if ( arguments[0].Year ) {
			this.Year = arguments[0].Year;
		}
		else 
			this.Year = null;//Date.parseDate(getGlobalOptions().date, 'd.m.Y').getFullYear();
		
		if ( arguments[0].onHide ) {
			this.onHide = arguments[0].onHide;
		}

		if ( arguments[0].formParams.Max_HealthKind_id ) {
			this.Max_HealthKind_id = arguments[0].formParams.Max_HealthKind_id;
		}

		if ( arguments[0].formParams.Not_Z_Group_Diag ) {
			this.Not_Z_Group_Diag = arguments[0].formParams.Not_Z_Group_Diag;
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
		
		this.findById('EVDDEW_PersonInformationFrame').load({
			Person_id: (arguments[0].Person_id ? arguments[0].Person_id : ''),
			Person_Birthday: (arguments[0].Person_Birthday ? arguments[0].Person_Birthday : ''),
			Person_Firname: (arguments[0].Person_Firname ? arguments[0].Person_Firname : ''),
			Person_Secname: (arguments[0].Person_Secname ? arguments[0].Person_Secname : ''),
			Person_Surname: (arguments[0].Person_Surname ? arguments[0].Person_Surname : ''),
			callback: function() {
				var field = base_form.findField('EvnVizitDispDop_setDate');
				clearDateAfterPersonDeath('personpanelid', 'EVDDEW_PersonInformationFrame', field);
			}
		});

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT });
		loadMask.show();

		// чистим фильтр у группы здоровья
		base_form.findField('HealthKind_id').getStore().clearFilter();

		// фильтруем сторе у специальности врача
		base_form.findField('DopDispSpec_id').getStore().clearFilter();
		base_form.findField('DopDispSpec_id').lastQuery = '';

		// 5. В осмотре сделать не доступным выбор "Специальность врача" = "Терапевт", если отсутствует хотя бы один осмотр остальных
		// специалистов (осмотр "8. Дополнительная консультация" в контроле не участвует) или отсутствует хотя бы одно лабораторное
		// исследование (исследование "18. Дополнительное исследование" в контроле не участвует).
		this.sex_id = arguments[0].Sex_id;
		this.age = arguments[0].Person_Age;
		this.UsedDopDispUslugaType = arguments[0].formParams['UsedDopDispUslugaType'];
		this.UsedDopDispSpec = arguments[0].formParams['UsedDopDispSpec'];
		this.Person_BirthDay = arguments[0].Person_Birthday;

		this.setDopDispSpecFilter();
		
		base_form.setValues(arguments[0].formParams);

		var diag_combo = base_form.findField('Diag_id');
		var dop_disp_spec_combo = base_form.findField('DopDispSpec_id');

		var lpu_section_id = arguments[0].formParams.LpuSection_id;
		var med_personal_id = arguments[0].formParams.MedPersonal_id;

		base_form.clearInvalid();

		switch ( this.action ) {
			case 'add':
				this.setTitle(WND_POL_EVDDADD);
				this.enableEdit(true);

				base_form.findField('EvnVizitDispDop_IsSanKur').setValue(1);
				base_form.findField('EvnVizitDispDop_IsOut').setValue(1);
				base_form.findField('DopDispAlien_id').setValue(1);
				var diag_id = 10940;
				// ограничиваем годом пришедшим извне.
				this.setMinDate(this.action);
				
				diag_combo.getStore().load({
					callback: function() {
						diag_combo.setValue(diag_id);
						diag_combo.fireEvent('select', diag_combo, diag_combo.getStore().getAt(0), 0);
					},
					params: {
						where: "where DiagLevel_id = 4 and Diag_id = " + diag_id
					}
				});

				dop_disp_spec_combo.fireEvent('change', dop_disp_spec_combo, dop_disp_spec_combo.getValue());

				loadMask.hide();

				base_form.findField('EvnVizitDispDop_setDate').focus(false, 250);
			break;

			case 'edit': case 'view':
			
				// если загружаем из рабочего места врача, то загружаем данные с сервера
				if ( arguments[0]['from'] && arguments[0]['from'] == 'workplace' )
				{
					base_form.load({
						failure: function() {
							loadMask.hide();
							sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_zagruzke_dannyih_formyi'], function() { this.hide(); }.createDelegate(this) );
						}.createDelegate(this),
						params: {
							EvnVizitDispDop_id: arguments[0]['EvnVizitDispDop_id'],
							archiveRecord: win.archiveRecord
						},
						success: function() {
							loadMask.hide();
							
							if ( base_form.findField('accessType').getValue() == 'view' ) {
								this.action = 'view';
							}
							
							this.setMinDate(this.action);
							
							var diag_id = diag_combo.getValue();

							if ( diag_id ) {
								diag_combo.getStore().load({
									callback: function() {
										diag_combo.setValue(diag_id);
										diag_combo.fireEvent('select', diag_combo, diag_combo.getStore().getAt(0), 0);
									},
									params: {
										where: "where DiagLevel_id = 4 and Diag_id = " + diag_id
									}
								});
							}

							dop_disp_spec_combo.fireEvent('change', dop_disp_spec_combo, dop_disp_spec_combo.getValue());
							
							var lpu_section_id = base_form.findField('LpuSection_id').getValue();
							var med_personal_id = base_form.findField('MedPersonal_id').getValue();

							// устанавливаем врача
							base_form.findField('MedStaffFact_id').getStore().findBy(function(record) {
								if ( record.get('MedPersonal_id') == med_personal_id ) {
									base_form.findField('MedStaffFact_id').setValue(record.get('MedStaffFact_id'));
									return true;
								}
							});							

							base_form.findField('LpuSection_id').setValue(lpu_section_id);
							
							dop_disp_spec_combo.fireEvent('change', dop_disp_spec_combo, dop_disp_spec_combo.getValue());
							
							base_form.clearInvalid();

							loadMask.hide();

							if ( this.action == 'edit' )
							{
								this.setTitle(WND_POL_EVDDEDIT);
								this.enableEdit(true);
								base_form.findField('EvnVizitDispDop_setDate').focus(false, 250);
							}
							else
							{
								this.setTitle(WND_POL_EVDDVIEW);
								this.enableEdit(false);
								this.buttons[3].focus();
							}
						}.createDelegate(this),
						url: '/?c=EvnPLDispDop&m=loadEvnVizitDispDopEditForm'																		
					});
				}
				else
				{
					loadMask.hide();
					var diag_id = diag_combo.getValue();

					if ( diag_id ) {
						diag_combo.getStore().load({
							callback: function() {
								diag_combo.setValue(diag_id);
								diag_combo.fireEvent('select', diag_combo, diag_combo.getStore().getAt(0), 0);
							},
							params: {
								where: "where DiagLevel_id = 4 and Diag_id = " + diag_id
							}
						});
					}

					dop_disp_spec_combo.fireEvent('change', dop_disp_spec_combo, dop_disp_spec_combo.getValue());

					// устанавливаем врача
					base_form.findField('MedStaffFact_id').getStore().findBy(function(record) {
						if ( record.get('MedPersonal_id') == med_personal_id ) {
							base_form.findField('MedStaffFact_id').setValue(record.get('MedStaffFact_id'));
							return true;
						}
					});
					this.setMinDate(this.action);
					base_form.findField('LpuSection_id').setValue(lpu_section_id);

					loadMask.hide();
					
					if ( this.action == 'edit' )
					{
						this.setTitle(WND_POL_EVDDEDIT);
						this.enableEdit(true);
						base_form.findField('EvnVizitDispDop_setDate').focus(false, 250);
					}
					else
					{
						this.setTitle(WND_POL_EVDDVIEW);
						this.enableEdit(false);
						this.buttons[3].focus();
					}
				}
			break;
/*			case 'view':
				current_window.setTitle(WND_POL_EVDDVIEW);
				current_window.enableEdit(false);

				var diag_combo = this.findById('EVDDEW_DiagCombo');
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
				Ext.getCmp('EVDDEW_DopDispSpecCombo').fireEvent('change', Ext.getCmp('EVDDEW_DopDispSpecCombo'), Ext.getCmp('EVDDEW_DopDispSpecCombo').getValue() );
				// устанавливаем врача
				form.findById('EVDDEW_MedPersonalCombo').getStore().findBy(function(record) {
					if ( record.get('MedPersonal_id') == med_personal_id )
					{
						form.findById('EVDDEW_MedPersonalCombo').setValue(record.get('MedStaffFact_id'));
						return true;
					}
				});		
				loadMask.hide();
				current_window.buttons[1].focus();
				this.findById('EVDDEW_LpuSectionCombo').setValue(lpu_section_id);
			break;
*/
		}

		this.selectSpecialist(); //приводим в порядок поля, блокируем все что нужно
		base_form.clearInvalid();
	},
	setMinDate: function(action)
	{
		if (action=='add')
		{
			// ограничиваем годом пришедшим извне.
			if (this.Year && this.Year>0)
			{
				this.findById('EVDDEW_EvnVizitDispDop_setDate').setMinValue(Date.parseDate('01.01.' + this.Year, 'd.m.Y'));
			}
			else 
			{
				this.findById('EVDDEW_EvnVizitDispDop_setDate').setMinValue(Date.parseDate('01.01.' + (Date.parseDate(getGlobalOptions().date, 'd.m.Y').getFullYear()-1), 'd.m.Y'));
			}
		}
		else 
		{
			var year = this.findById('EVDDEW_EvnVizitDispDop_setDate').getValue().getFullYear();
			if (year)
			{
				this.findById('EVDDEW_EvnVizitDispDop_setDate').setMinValue(Date.parseDate('01.01.' + year, 'd.m.Y'));
			}
		}
	},
	width: 700
});