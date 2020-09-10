/**
* swEvnVizitDispTeen14EditWindow - окно редактирования/добавления осмотра по диспансеризации подростков 14ти лет
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package		Polka
* @access		public
* @copyright	Copyright (c) 2009 - 2011 Swan Ltd.
* @author		Ivan Pshenitcyn aka IVP (ipshon@gmail.com)
* @version		01.08.2011
* @comment		Префикс для id компонентов EVDT14EW (swEvnVizitDispTeen14EditWindow)
*				tabIndex: TABINDEX_EVDT14EW
*
*
* Использует: окно редактирования талона по доп. диспансеризации (swEvnPLDispTeen14EditWindow)
*/
/*NO PARSE JSON*/
sw.Promed.swEvnVizitDispTeen14EditWindow = Ext.extend(sw.Promed.BaseForm, {
	action: null,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	collapsible: false,
	/* */
	codeRefresh: true,
	objectName: 'swEvnVizitDispTeen14EditWindow',
	objectSrc: '/jscore/Forms/Polka/swEvnVizitDispTeen14EditWindow.js',
	
	doSave: function() {
		var add_flag = true;
        var current_window = this;
		var index = -1;
		var dopdisp_spec_id = this.findById('EVDT14EW_Teen14DispSpecTypeCombo').getValue();
		var dopdisp_spec_name = '';
		var lpu_section_id = this.findById('EVDT14EW_LpuSectionCombo').getValue();
		var lpu_section_name = '';
		var med_staff_fact_id = this.findById('EVDT14EW_MedPersonalCombo').getValue();

		this.findById('EVDT14EW_DiagCombo').fireEvent('blur', this.findById('EVDT14EW_DiagCombo'));

		var diag_id = this.findById('EVDT14EW_DiagCombo').getValue();
		var diag_code = '';
		var dopdispdiagtype_id = this.findById('EVDT14EW_DopDispDiagTypeCombo').getValue();
		var deseasestage_id = this.findById('EVDT14EW_DeseaseStageCombo').getValue();
		var healthkind_id = this.findById('EVDT14EW_HealthKindCombo').getValue();
		var issankur_id = this.findById('EVDT14EW_EvnVizitDispTeen14_IsSanKurCombo').getValue();
		var isout_id = this.findById('EVDT14EW_EvnVizitDispTeen14_IsOutCombo').getValue();
		var isalien_id = Ext.getCmp('EVDT14EW_DopDispAlien_idCombo').getValue();
		var recommendations = this.findById('EVDT14EW_EvnVizitDispTeen14_Descr').getValue();

		var record_status = this.findById('EVDT14EW_Record_Status').getValue();

        //Проверка на наличие у врача кода ДЛО или специальности https://redmine.swan.perm.ru/issues/47172
        if(getRegionNick().inlist([ 'kareliya' ])){
            var MedSpecOms_id = '';
            var MedPersonal_DloCode = '';
            current_window.findById('EVDT14EW_MedPersonalCombo').getStore().findBy(function(record) {
                if ( record.get('MedStaffFact_id') == med_staff_fact_id )
                {
                    MedSpecOms_id = (!Ext.isEmpty(record.get('MedSpecOms_id'))) ? record.get('MedSpecOms_id') : '';
                    MedPersonal_DloCode = (!Ext.isEmpty(record.get('MedPersonal_DloCode'))) ? record.get('MedPersonal_DloCode') : '';
                }
            });
            if(MedSpecOms_id == ''){
                Ext.Msg.alert(lang['soobschenie'], lang['u_vracha_ne_ukazana_spetsialnost'], function() {  current_window.findById('EVDT14EW_MedPersonalCombo').clearValue(); } );
                return false;
            }
            if(MedPersonal_DloCode == ''){
                Ext.Msg.alert(lang['soobschenie'], lang['u_vracha_ne_ukazan_kod_dlo'], function() {  current_window.findById('EVDT14EW_MedPersonalCombo').clearValue(); } );
                return false;
            }
        }
		if ( !this.findById('EvnVizitDispTeen14EditForm').getForm().isValid() )
		{
			Ext.MessageBox.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					this.findById('EvnVizitDispTeen14EditForm').getFirstInvalidEl().focus(true);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
		
		if ( (this.findById('EVDT14EW_EvnVizitDispTeen14_setDate').getValue() < Date.parseDate('01.03.2011', 'd.m.Y')) )
		{
			Ext.MessageBox.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					this.findById('EVDT14EW_EvnVizitDispTeen14_setDate').focus(false);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: "При проведении диспансеризации 14 - летних подростков не могут использоваться медицинских осмотры, проведенные ранее 4 месяцев с момента начала диспансеризации.",
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}
		var set_date = this.findById('EVDT14EW_EvnVizitDispTeen14_setDate').getValue();
		var birth_date = this.Person_BirthDay;
		var age = (birth_date.getMonthsBetween(set_date) - (birth_date.getMonthsBetween(set_date) % 12)) / 12;		
		if ( dopdisp_spec_id == 1 && age != 14 )
		{
			Ext.MessageBox.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					this.findById('EVDT14EW_EvnVizitDispTeen14_setDate').focus(false);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: "Диспансеризация подростков 14 лет проводится только для подростков строго 14 лет.",
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		if ( record_status == 1 )
		{
			record_status = 2;
		}

		index = this.findById('EVDT14EW_Teen14DispSpecTypeCombo').getStore().findBy(function(rec) { return rec.get('Teen14DispSpecType_id') == dopdisp_spec_id; });
		if (index >= 0)
		{
			dopdisp_spec_name = this.findById('EVDT14EW_Teen14DispSpecTypeCombo').getStore().getAt(index).data.Teen14DispSpecType_Name;
		}
		
		index = this.findById('EVDT14EW_LpuSectionCombo').getStore().findBy(function(rec) { return rec.get('LpuSection_id') == lpu_section_id; });
		if (index >= 0)
		{
			lpu_section_name = this.findById('EVDT14EW_LpuSectionCombo').getStore().getAt(index).data.LpuSection_Name;
		}

		
		record = this.findById('EVDT14EW_MedPersonalCombo').getStore().getById(med_staff_fact_id);
		if ( record ) {
			med_personal_fio = record.get('MedPersonal_Fio');
			med_personal_id = record.get('MedPersonal_id');
		} else {
			med_personal_fio = null;
			med_personal_id = null;
		}				

		index = this.findById('EVDT14EW_DiagCombo').getStore().findBy(function(rec) { return rec.get('Diag_id') == diag_id; });
		if (index >= 0)
		{
			diag_code = this.findById('EVDT14EW_DiagCombo').getStore().getAt(index).data.Diag_Code + ' ' + this.findById('EVDT14EW_DiagCombo').getStore().getAt(index).data.Diag_Name;
		}
		
		if ( this.action != 'add' )
		{
			add_flag = false;
		}

		var data = [{
			'EvnVizitDispTeen14_id': this.findById('EVDT14EW_EvnVizitDispTeen14_id').getValue(),
			'Teen14DispSpecType_id': dopdisp_spec_id,
			'LpuSection_id': lpu_section_id,
			'MedPersonal_id': med_personal_id,
			'MedStaffFact_id': med_staff_fact_id,
			'Diag_id': diag_id,
			'Teen14DispSpecType_Name': dopdisp_spec_name,
			'EvnVizitDispTeen14_setDate': this.findById('EVDT14EW_EvnVizitDispTeen14_setDate').getValue(),
			'LpuSection_Name': lpu_section_name,
			'MedPersonal_Fio': med_personal_fio,
			'Diag_Code': diag_code,
			'DopDispDiagType_id': dopdispdiagtype_id,
			'DeseaseStage_id': deseasestage_id,
			'HealthKind_id': healthkind_id,
			'EvnVizitDispTeen14_IsSanKur': issankur_id,
			'EvnVizitDispTeen14_IsOut': isout_id,
			'DopDispAlien_id': isalien_id,
			'EvnVizitDispTeen14_Descr': recommendations,
			'Record_Status': record_status
		}];

		this.callback(data, add_flag);
		this.hide();
	},
	draggable: true,
	enableEdit: function(enable) {
		if (enable)
		{
			this.findById('EVDT14EW_EvnVizitDispTeen14_setDate').enable();
			this.findById('EVDT14EW_LpuSectionCombo').enable();
			this.findById('EVDT14EW_Teen14DispSpecTypeCombo').enable();
			this.findById('EVDT14EW_MedPersonalCombo').enable();
			this.findById('EVDT14EW_DiagCombo').enable();
			this.findById('EVDT14EW_DopDispDiagTypeCombo').enable();
			this.findById('EVDT14EW_DeseaseStageCombo').enable();
			this.findById('EVDT14EW_HealthKindCombo').enable();
			this.findById('EVDT14EW_EvnVizitDispTeen14_IsSanKurCombo').enable();
			this.findById('EVDT14EW_EvnVizitDispTeen14_IsOutCombo').enable();
			this.findById('EVDT14EW_DopDispAlien_idCombo').enable();
			this.findById('EVDT14EW_EvnVizitDispTeen14_Descr').enable();

			// enable() для кнопок на гридах

			this.buttons[0].show();
		}
		else
		{
			this.findById('EVDT14EW_EvnVizitDispTeen14_setDate').disable();
			this.findById('EVDT14EW_LpuSectionCombo').disable();
			this.findById('EVDT14EW_Teen14DispSpecTypeCombo').disable();
			this.findById('EVDT14EW_MedPersonalCombo').disable();
			this.findById('EVDT14EW_DiagCombo').disable();
			this.findById('EVDT14EW_DopDispDiagTypeCombo').disable();
			this.findById('EVDT14EW_DeseaseStageCombo').disable();
			this.findById('EVDT14EW_HealthKindCombo').disable();
			this.findById('EVDT14EW_EvnVizitDispTeen14_IsSanKurCombo').disable();
			this.findById('EVDT14EW_EvnVizitDispTeen14_IsOutCombo').disable();
			this.findById('EVDT14EW_DopDispAlien_idCombo').disable();
			this.findById('EVDT14EW_EvnVizitDispTeen14_Descr').disable();

			// disable() для кнопок на гридах

			this.buttons[0].hide();
		}
	},
	height: 455,
	id: 'EvnVizitDispTeen14EditWindow',
	initComponent: function() {
        var win = this;
		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.doSave();
				}.createDelegate(this),
				iconCls: 'save16',
				id: 'EVDT14EW_SaveButton',
				tabIndex: TABINDEX_EVDT14EW + 12,
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
				id: 'EVDT14EW_CancelButton',
				onTabAction: function() {
					this.findById('EVDT14EW_EvnVizitDispTeen14_setDate').focus(true, 200);
				}.createDelegate(this),
				onShiftTabAction: function() {
					this.findById('EVDT14EW_SaveButton').focus(true, 200);
				}.createDelegate(this),
				tabIndex: TABINDEX_EVDT14EW + 13,
				text: BTN_FRMCANCEL
			}],
			items: [
				new sw.Promed.PersonInformationPanelShort({
					id: 'EVDT14EW_PersonInformationFrame',
					region: 'north'
				}),
				new Ext.form.FormPanel({
					bodyBorder: false,
					bodyStyle: 'padding: 5px 5px 0',
					border: false,
					frame: false,
					id: 'EvnVizitDispTeen14EditForm',
					labelAlign: 'right',
					labelWidth: 150,
					items: [{
						id: 'EVDT14EW_EvnVizitDispTeen14_id',
						name: 'EvnVizitDispTeen14_id',
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
						id: 'EVDT14EW_Record_Status',
						name: 'Record_Status',
						value: 0,
						xtype: 'hidden'
					}, {
						allowBlank: false,
						enableKeyEvents: true,
						fieldLabel: lang['data_osmotra'],
						format: 'd.m.Y',
						id: 'EVDT14EW_EvnVizitDispTeen14_setDate',
						listeners: {
							'keydown':  function(inp, e) {
								if ( e.shiftKey && e.getKey() == Ext.EventObject.TAB )
								{
									e.stopEvent();
									Ext.getCmp('EVDT14EW_CancelButton').focus(true, 200);
								}
							},
							'change': function(field, newValue, oldValue) {
								if (blockedDateAfterPersonDeath('personpanelid', 'EVDT14EW_PersonInformationFrame', field, newValue, oldValue)) return;
								
								this.setTeen14DispSpecTypeFilter();
								this.selectSpecialist();								
							}.createDelegate(this)
						},
						maxValue: Date.parseDate(getGlobalOptions().date, 'd.m.Y'),
						//minValue: Date.parseDate('01.01.' + Date.parseDate(getGlobalOptions().date, 'd.m.Y').getFullYear(), 'd.m.Y'),
						name: 'EvnVizitDispTeen14_setDate',
						plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
						tabIndex: TABINDEX_EVDT14EW + 1,
						width: 100,
						xtype: 'swdatefield'
					}, {
						allowBlank: false,
						enableKeyEvents: true,
						fieldLabel: lang['spetsialnost_vracha'],
						hiddenName: 'Teen14DispSpecType_id',
						id: 'EVDT14EW_Teen14DispSpecTypeCombo',
						listeners: {
							'change': function(combo, newValue) {
								this.selectSpecialist();
							}.createDelegate(this)
						},
						tabIndex: TABINDEX_EVDT14EW + 2,
						validateOnBlur: false,
						width: 350,
						xtype: 'swteen14dispspectypecombo'
					},{
						allowBlank: false,
						fieldLabel: lang['storonniy_spetsialist'],
						autoLoad: true,
						lastQuery: '',
						id: 'EVDT14EW_DopDispAlien_idCombo',
						hiddenName: 'DopDispAlien_id',
						tabIndex: TABINDEX_EVDT14EW + 3,
						width: 200,
						xtype: 'swdopdispaliencombo',
						listeners: {
							'change': function(combo, newValue) {
								this.selectSpecialist();
							}.createDelegate(this),
							'render': function(combo) {
								combo.getStore().load();
							}
						}

					}, {
						allowBlank: false,
						hiddenName: 'LpuSection_id',
						id: 'EVDT14EW_LpuSectionCombo',
						lastQuery: '',
						listWidth: 650,
						linkedElements: [
							'EVDT14EW_MedPersonalCombo'
						],
						tabIndex: TABINDEX_EVDT14EW + 4,
						width: 450,
						xtype: 'swlpusectionglobalcombo'
					}, {
						allowBlank: false,
						hiddenName: 'MedStaffFact_id',
						id: 'EVDT14EW_MedPersonalCombo',
						lastQuery: '',
						listWidth: 650,
						parentElementId: 'EVDT14EW_LpuSectionCombo',
                        listeners: {
                            'change': function(field, newValue, oldValue) {
                                if(getRegionNick().inlist([ 'kareliya' ])) {
                                    var MedSpecOms_id = '';
                                    var MedPersonal_DloCode = '';
									var MedPersonal_Snils = '';
                                    win.findById('EVDT14EW_MedPersonalCombo').getStore().findBy(function(record) {
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
                                            Ext.Msg.alert(lang['soobschenie'], lang['u_vracha_ne_ukazana_spetsialnost'], function() {  win.findById('EVDT14EW_MedPersonalCombo').clearValue(); } );
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
						tabIndex: TABINDEX_EVDT14EW + 5,
						width: 450,
						xtype: 'swmedstafffactglobalcombo'
					}, {
						allowBlank: false,
						hiddenName: 'Diag_id',
						id: 'EVDT14EW_DiagCombo',
						listWidth: 580,
						tabIndex: TABINDEX_EVDT14EW + 6,
						width: 450,
						xtype: 'swdiagcombo'
					}, {
						allowBlank: false,
						enableKeyEvents: true,
						fieldLabel: lang['zabolevanie'],
						hiddenName: 'DopDispDiagType_id',
						id: 'EVDT14EW_DopDispDiagTypeCombo',
						tabIndex: TABINDEX_EVDT14EW + 7,
						validateOnBlur: false,
						width: 350,
						xtype: 'swdopdispdiagtypecombo'
					}, {
						allowBlank: false,
						enableKeyEvents: true,
						fieldLabel: lang['stadiya_zabolevaniya'],
						id: 'EVDT14EW_DeseaseStageCombo',
						name: 'DeseaseStage_id',
						tabIndex: TABINDEX_EVDT14EW + 8,
						validateOnBlur: false,
						width: 350,
						xtype: 'swdeseasestagecombo'
					}, {
						allowBlank: false,
						enableKeyEvents: true,
						fieldLabel: lang['gruppa_zdorovya'],
						hiddenName: 'HealthKind_id',
						id: 'EVDT14EW_HealthKindCombo',
						lastQuery: '',
						listeners: {
							'render': function(combo) {
								combo.getStore().load();
							}
						},
						tabIndex: TABINDEX_EVDT14EW + 9,
						validateOnBlur: false,
						width: 350,
						xtype: 'swhealthkindcombo'
					}, {
						allowBlank: false,
						fieldLabel: lang['na_san-kur_lechenie'],
						hiddenName: 'EvnVizitDispTeen14_IsSanKur',
						id: 'EVDT14EW_EvnVizitDispTeen14_IsSanKurCombo',
						tabIndex: TABINDEX_EVDT14EW + 10,
						width: 200,
						xtype: 'swyesnocombo'
					}, {
						allowBlank: false,
						fieldLabel: lang['vyiezdnoe'],
						hiddenName: 'EvnVizitDispTeen14_IsOut',
						id: 'EVDT14EW_EvnVizitDispTeen14_IsOutCombo',
						tabIndex: TABINDEX_EVDT14EW + 11,
						width: 200,
						xtype: 'swyesnocombo'
					}, {
						allowBlank: true,
						fieldLabel: lang['rekomendatsii'],
						hiddenName: 'EvnVizitDispTeen14_Descr',
						name: 'EvnVizitDispTeen14_Descr',
						id: 'EVDT14EW_EvnVizitDispTeen14_Descr',						
						tabIndex: TABINDEX_EVDT14EW + 12,
						width: 450,
						xtype: 'textarea'
					}],
					layout: 'form',
					reader: new Ext.data.JsonReader({
						success: function() { }
					}, [
						{ name: 'EvnVizitDispTeen14_id' },
						{ name: 'Record_Status' },
						{ name: 'EvnVizitDispTeen14_setDate' },
						{ name: 'Teen14DispSpecType_id' },
						{ name: 'accessType' },
						{ name: 'DopDispAlien_id' },
						{ name: 'LpuSection_id' },
						{ name: 'MedPersonal_id'},
						{ name: 'Diag_id' },
						{ name: 'DopDispDiagType_id' },
						{ name: 'DeseaseStage_id' },
						{ name: 'HealthKind_id' },
						{ name: 'EvnVizitDispTeen14_IsSanKur' },
						{ name: 'EvnVizitDispTeen14_IsOut' },
						{ name: 'EvnVizitDispTeen14_Descr' }
					]),
					region: 'center'
				})
			]
		});	
		sw.Promed.swEvnVizitDispTeen14EditWindow.superclass.initComponent.apply(this, arguments);

		this.findById('EVDT14EW_DiagCombo').onChange = function(combo, newValue, oldValue) {
			var base_form = this.findById('EvnVizitDispTeen14EditForm').getForm();

			var desease_stage_combo = base_form.findField('DeseaseStage_id');
			var dop_disp_diag_type_combo = base_form.findField('DopDispDiagType_id');
			var dop_disp_spec_combo = base_form.findField('Teen14DispSpecType_id');
			var health_kind_combo = base_form.findField('HealthKind_id');

			var desease_stage_id = desease_stage_combo.getValue();
			var dop_disp_diag_type_id = dop_disp_diag_type_combo.getValue();
			var dop_disp_spec_id = dop_disp_spec_combo.getValue().toString();
			var health_kind_id = health_kind_combo.getValue();

					//health_kind_combo.clearValue();
			//health_kind_combo.disable();
					//desease_stage_combo.clearValue();
			//desease_stage_combo.disable();
					//dop_disp_diag_type_combo.clearValue();
			//dop_disp_diag_type_combo.disable();

			var record = combo.getStore().getById(newValue);

			if ( !record || !dop_disp_spec_id ) {
				return false;
			}

			diag_code = record.get('Diag_Code').substr(0, 1);
			
			var groupDiagZ00 = record.get('Diag_Code').substr(0, 3).inlist(['Z00']);
			
			health_kind_combo.getStore().clearFilter();
			//health_kind_combo.enable();
			
			var groupDiagZ = (diag_code.inlist(['Z']));
			dop_disp_diag_type_combo.setAllowBlank(groupDiagZ);
			desease_stage_combo.setAllowBlank(groupDiagZ);
			//health_kind_combo.setDisabled(groupDiagZ00);
			dop_disp_diag_type_combo.setDisabled(groupDiagZ00);
			desease_stage_combo.setDisabled(groupDiagZ00);
			
			if ( groupDiagZ ) {
				if ( dop_disp_spec_id != '1' ) {
					//health_kind_combo.disable(); 
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
				//desease_stage_combo.enable();
				//dop_disp_diag_type_combo.enable();

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
				//desease_stage_combo.enable();
				//dop_disp_diag_type_combo.enable();

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

			var current_window = Ext.getCmp('EvnVizitDispTeen14EditWindow');

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
		var base_form = this.findById('EvnVizitDispTeen14EditForm').getForm();
		// проверяем сан. кур. лечение
		var Teen14DispSpecType_id = base_form.findField('Teen14DispSpecType_id').getValue();
		var DopDispAlien_id = base_form.findField('DopDispAlien_id').getValue();
		/*if ( Teen14DispSpecType_id == 1 && this.EvnVizitDispTeen14_IsSanKur_Test ) {
			base_form.findField('EvnVizitDispTeen14_IsSanKur').setValue(2);
			base_form.findField('EvnVizitDispTeen14_IsSanKur').disable();
		} else  {
			base_form.findField('EvnVizitDispTeen14_IsSanKur').enable();
		}*/
		// base_form.findField('Diag_id').additQueryFilter = false;

		base_form.findField('Diag_id').onChange(base_form.findField('Diag_id'), base_form.findField('Diag_id').getValue());

		if ( Teen14DispSpecType_id > 0 )
		{
			var lpu_section_id = base_form.findField('LpuSection_id').getValue();
			var med_staff_fact_id = base_form.findField('MedStaffFact_id').getValue();
			
			base_form.findField('LpuSection_id').clearValue();
			base_form.findField('MedStaffFact_id').clearValue();

			var set_date = base_form.findField('EvnVizitDispTeen14_setDate').getValue();
			
			var section_filter_params = {
				isPolkaAndStom: true
			};									
			var medstafffact_filter_params = {
				isPolkaAndStom: true
			};

			section_filter_params.isAliens = (DopDispAlien_id == 2);
			medstafffact_filter_params.isAliens = (DopDispAlien_id == 2);
			
			if ( Teen14DispSpecType_id )
			{
				switch ( Teen14DispSpecType_id )
				{
					/* Для Уфы, профили отделений описаны в задаче #4246 */
					/* savage: обновленная информация в задаче https://redmine.swan.perm.ru/issues/6543 */
					case 1:
						if ( getGlobalOptions().region && getGlobalOptions().region.nick == 'ufa' )
						{
							// врач-педиатр
							section_filter_params.arrayLpuSectionProfile = [ '531', '631' ];
							medstafffact_filter_params.arrayLpuSectionProfile = [ '531', '631' ];
						}
						else
						{
							// врач-педиатр
							section_filter_params.arrayLpuSectionProfile = ['916'];
							medstafffact_filter_params.arrayLpuSectionProfile = ['916'];
						}
					break;

					case 2:
						if ( getGlobalOptions().region && getGlobalOptions().region.nick == 'ufa' )
						{
							// врач-невролог
							section_filter_params.arrayLpuSectionProfile = [ '509', '537', '609', '637' ];
							medstafffact_filter_params.arrayLpuSectionProfile = [ '509', '537', '609', '637' ];
						}
						else
						{
							// врач-невролог
							section_filter_params.arrayLpuSectionProfile = ['2800', '2805'];
							medstafffact_filter_params.arrayLpuSectionProfile = ['2800', '2805'];
						}
					break;

					case 3:
						if ( getGlobalOptions().region && getGlobalOptions().region.nick == 'ufa' )
						{
							// врач-офтальмолог
							section_filter_params.arrayLpuSectionProfile = [ '518', '542', '618', '642' ];
							medstafffact_filter_params.arrayLpuSectionProfile = [ '518', '542', '618', '642' ];
						}
						else
						{
							// врач-офтальмолог
							section_filter_params.arrayLpuSectionProfile = ['2700'];
							medstafffact_filter_params.arrayLpuSectionProfile = ['2700'];
						}
					break;

					case 4:
						if ( getGlobalOptions().region && getGlobalOptions().region.nick == 'ufa' )
						{
							// врач-хирург (врач-детский хирург)
							section_filter_params.arrayLpuSectionProfile = [ '510', '536', '610', '636' ];
							medstafffact_filter_params.arrayLpuSectionProfile = [ '510', '536', '610', '636' ];
						}
						else
						{
							// врач-хирург (врач-детский хирург)
							section_filter_params.arrayLpuSectionProfile = ['2300', '2350'];
							medstafffact_filter_params.arrayLpuSectionProfile = ['2300', '2350'];
						}
					break;

					case 5:
						if ( getGlobalOptions().region && getGlobalOptions().region.nick == 'ufa' )
						{
							// врач-отоларинголог
							section_filter_params.arrayLpuSectionProfile = [ '517', '541', '617', '641' ];
							medstafffact_filter_params.arrayLpuSectionProfile = [ '517', '541', '617', '641' ];
						}
						else
						{
							// врач-отоларинголог
							section_filter_params.arrayLpuSectionProfile = ['2600'];
							medstafffact_filter_params.arrayLpuSectionProfile = ['2600'];
						}
					break;

					case 6:
						if ( getGlobalOptions().region && getGlobalOptions().region.nick == 'ufa' )
						{
							// врач-акушер-гинеколог
							section_filter_params.arrayLpuSectionProfile = [ '522', '540', '622', '640' ];
							medstafffact_filter_params.arrayLpuSectionProfile = [ '522', '540', '622', '640' ];
						}
						else
						{
							// врач-акушер-гинеколог
							section_filter_params.arrayLpuSectionProfile = ['2517', '2504', '2518', '2519'];
							medstafffact_filter_params.arrayLpuSectionProfile = ['2517', '2504', '2518', '2519'];
						}						
					break;

					case 7:
						if ( getGlobalOptions().region && getGlobalOptions().region.nick == 'ufa' )
						{
							// врач-уролог
							section_filter_params.arrayLpuSectionProfile = [ '520', '539', '536', '620', '639', '636' ];
							medstafffact_filter_params.arrayLpuSectionProfile = [ '520', '539', '536', '620', '639', '636' ];
						}
						else
						{
							// врач-уролог
							section_filter_params.arrayLpuSectionProfile = ['1500', '1530', '1503'];
							medstafffact_filter_params.arrayLpuSectionProfile = ['1500', '1530', '1503'];
						}						
					break;

					case 8:
						if ( getGlobalOptions().region && getGlobalOptions().region.nick == 'ufa' )
						{
							// врач-эндокринолог (врач-детский эндокринолог)
							section_filter_params.arrayLpuSectionProfile = [ '508', '534', '608', '634' ];
							medstafffact_filter_params.arrayLpuSectionProfile = [ '508', '534', '608', '634' ];
						}
						else
						{
							// врач-эндокринолог (врач-детский эндокринолог)
							section_filter_params.arrayLpuSectionProfile = ['0510', '0530', '510', '530'];
							medstafffact_filter_params.arrayLpuSectionProfile = ['0510', '0530', '510', '530'];
						}
					break;

					case 9:
						if ( getGlobalOptions().region && getGlobalOptions().region.nick == 'ufa' )
						{
							// врач-ортопед-травматолог
							section_filter_params.arrayLpuSectionProfile = [ '514', '550', '614', '615', '650', '651' ];
							medstafffact_filter_params.arrayLpuSectionProfile = [ '514', '550', '614', '615', '650', '651' ];
						}
						else
						{
							// врач-стоматолог (врач-детский стоматолог)
							section_filter_params.arrayLpuSectionProfile = ['1830', '1810', '1800'];
							medstafffact_filter_params.arrayLpuSectionProfile = ['1830', '1810', '1800'];
							section_filter_params.isPolkaAndStom = true;
							medstafffact_filter_params.isPolkaAndStom = true;
						}
					break;

					case 10:
						if ( getGlobalOptions().region && getGlobalOptions().region.nick == 'ufa' )
						{
							// врач-стоматолог (врач-детский стоматолог)
							section_filter_params.arrayLpuSectionProfile = [ '529', '530', '629', '630' ];
							medstafffact_filter_params.arrayLpuSectionProfile = [ '529', '530', '629', '630' ];
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
				base_form.findField('LpuSection_id').fireEvent('change', base_form.findField('LpuSection_id'), lpu_section_id);
			}

			if ( base_form.findField('MedStaffFact_id').getStore().getById(med_staff_fact_id) ) {
				base_form.findField('MedStaffFact_id').setValue(med_staff_fact_id);
			}
			
			/*
				если форма открыта на редактирование и задано отделение и 
				место работы или задан список мест работы, то не даем редактировать вообще
			*/
			if ( this.action == 'edit' && (( user_med_staff_fact_id && user_lpu_section_id ) || ( this.UserMedStaffFacts && this.UserMedStaffFacts.length > 0 )) )
			{
				base_form.findField('LpuSection_id').disable();
				base_form.findField('MedStaffFact_id').disable();
			}
			
			/*
				если форма открыта на добавление и задано отделение и 
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
					если форма открыта на добавление и задан список отделений и 
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
				
			/*
			 * при добавлении осмотра врача специалиста, если в поле "сторонний специалист" выбрано значение "да"
			 * или "предыдущий медосмотр", то поля "отделение" и "врач" не обязательны для заполнения. 
			 */
			if ( DopDispAlien_id == 1 )
			{
				base_form.findField('LpuSection_id').setAllowBlank(false);
				base_form.findField('MedStaffFact_id').setAllowBlank(false);
			}
			else
			{
				base_form.findField('LpuSection_id').setAllowBlank(true);
				base_form.findField('MedStaffFact_id').setAllowBlank(true);
			}
			// Это для всех врачей, кроме педиатра, для него всегда "сторонний специалист" - "нет" и недоступно для выбора
			if ( Teen14DispSpecType_id == 1 )
			{
				base_form.findField('DopDispAlien_id').setValue(1);
				base_form.findField('DopDispAlien_id').disable();
			}
			else if ( this.action != 'view' )
			{
				base_form.findField('DopDispAlien_id').enable();
			}
			/*if (DopDispAlien_id == 3) { //если предыдущий медосмотр, блокируем поле врач и делаем его необязательным.
				base_form.findField('MedStaffFact_id').allowBlank = true;
				base_form.findField('MedStaffFact_id').disable();
			} else {
				base_form.findField('MedStaffFact_id').allowBlank = false;
				if (base_form.findField('MedStaffFact_id').disabled) {
					base_form.findField('MedStaffFact_id').disable();
				} else {
					base_form.findField('MedStaffFact_id').enable();
				}
			}*/
		}
	},
	setTeen14DispSpecTypeFilter: function() {
		var set_date = this.findById('EVDT14EW_EvnVizitDispTeen14_setDate').getValue();
		if ( !set_date || set_date == '' )
			var age = this.age;
		else
		{			
			var birth_date = this.Person_BirthDay;
			var age = (birth_date.getMonthsBetween(set_date) - (birth_date.getMonthsBetween(set_date) % 12)) / 12;			
		}
		var sex_id = this.sex_id;
		var UsedTeen14DispSpecType = this.UsedTeen14DispSpecType ? this.UsedTeen14DispSpecType : [];
		var UsedDopDispUslugaType = this.UsedDopDispUslugaType ? this.UsedDopDispUslugaType : [];
		var base_form = this.findById('EvnVizitDispTeen14EditForm').getForm();
		base_form.findField('Teen14DispSpecType_id').getStore().clearFilter();
		base_form.findField('Teen14DispSpecType_id').lastQuery = '';
		
		var max_count = (getGlobalOptions().region && getGlobalOptions().region.nick == 'ufa' ? 8 : 6);
		var count = 0;

		for ( i = 0; i < UsedTeen14DispSpecType.length; i++ ) {
			if ( UsedTeen14DispSpecType[i] == 1 && i != 1 ) {
				// Для Уфы все осмотры являются обязательным
				if ( getGlobalOptions().region && getGlobalOptions().region.nick == 'ufa' ) {
					count++;
				}
				// Для остальных регионов осмотр врача-стоматолога не считается обязательным
				else if ( i != 9 ) {
					count++;
				}
			}
		}

		var spec_is_full = (count >= max_count);

		base_form.findField('Teen14DispSpecType_id').getStore().filterBy(function(record) {
			if ( record.get('Teen14DispSpecType_Code') == 1 && !spec_is_full ) {
				return false;
			}

			if ( UsedTeen14DispSpecType[record.get('Teen14DispSpecType_id')] == 1 ) {
				return false;
			}

			if ( record.get('Teen14DispSpecType_Code') == 6 && sex_id == 1 ) {
				return false;
			}
			
			if ( record.get('Teen14DispSpecType_Code') == 7 && sex_id == 2 ) {
				return false;
			}

			return true;
		});

		if ( base_form.findField('Teen14DispSpecType_id').getStore().getCount() == 1 ) {
			base_form.findField('Teen14DispSpecType_id').setValue(base_form.findField('Teen14DispSpecType_id').getStore().getAt(0).get('Teen14DispSpecType_id'));
			base_form.findField('Teen14DispSpecType_id').fireEvent('change', base_form.findField('Teen14DispSpecType_id'), base_form.findField('Teen14DispSpecType_id').getValue());
		}
	},
	temparg: new Object(),
	show: function() {
		sw.Promed.swEvnVizitDispTeen14EditWindow.superclass.show.apply(this, arguments);

		this.center();

		var base_form = this.findById('EvnVizitDispTeen14EditForm').getForm();
		base_form.reset();

		this.action = null;
		this.callback = Ext.emptyFn;
		//this.EvnVizitDispTeen14_IsSanKur_Test = false;
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

		/*if ( arguments[0].formParams.EvnVizitDispTeen14_IsSanKur_Test ) {
			this.EvnVizitDispTeen14_IsSanKur_Test = arguments[0].formParams.EvnVizitDispTeen14_IsSanKur_Test;
		}*/

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
		/*
		if ( arguments[0].formParams.Not_Z_Group_Diag ) {
			this.Not_Z_Group_Diag = arguments[0].formParams.Not_Z_Group_Diag;
		}*/
		
		// определенный медстафффакт
		/*if ( arguments[0].UserMedStaffFact_id && arguments[0].UserMedStaffFact_id > 0 )
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
		}*/
		
		// свободный выбор врача и отделения
		this.UserMedStaffFacts = null;
		this.UserLpuSections = null;
		
		// определенный LpuSection
		/*if ( arguments[0].UserLpuSection_id && arguments[0].UserLpuSection_id > 0 )
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
		}*/
		
		// свободный выбор врача и отделения
		this.UserLpuSectons = null;
		
		this.findById('EVDT14EW_PersonInformationFrame').load({
			Person_id: (arguments[0].Person_id ? arguments[0].Person_id : ''),
			Person_Birthday: (arguments[0].Person_Birthday ? arguments[0].Person_Birthday : ''),
			Person_Firname: (arguments[0].Person_Firname ? arguments[0].Person_Firname : ''),
			Person_Secname: (arguments[0].Person_Secname ? arguments[0].Person_Secname : ''),
			Person_Surname: (arguments[0].Person_Surname ? arguments[0].Person_Surname : ''),
			callback: function() {
				var field = base_form.findField('EvnVizitDispTeen14_setDate');
				clearDateAfterPersonDeath('personpanelid', 'EVDT14EW_PersonInformationFrame', field);
			}
		});

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT });
		loadMask.show();

		// чистим фильтр у группы здоровья
		base_form.findField('HealthKind_id').getStore().clearFilter();

		// фильтруем сторе у специальности врача
		base_form.findField('Teen14DispSpecType_id').getStore().clearFilter();
		base_form.findField('Teen14DispSpecType_id').lastQuery = '';

		// 5. В осмотре сделать не доступным выбор "Специальность врача" = "Терапевт", если отсутствует хотя бы один осмотр остальных
		// специалистов (осмотр "8. Дополнительная консультация" в контроле не участвует) или отсутствует хотя бы одно лабораторное
		// исследование (исследование "18. Дополнительное исследование" в контроле не участвует).
		this.sex_id = arguments[0].Sex_id;
		this.age = arguments[0].Person_Age;
		this.UsedDopDispUslugaType = arguments[0].formParams['UsedDopDispUslugaType'];
		this.UsedTeen14DispSpecType = arguments[0].formParams['UsedTeen14DispSpecType'];
		this.Person_BirthDay = arguments[0].Person_Birthday;

		this.setTeen14DispSpecTypeFilter();
		
		base_form.setValues(arguments[0].formParams);
		
		var is_alien_id = arguments[0].formParams['DopDispAlien_id'];

		var diag_combo = base_form.findField('Diag_id');
		var dop_disp_spec_combo = base_form.findField('Teen14DispSpecType_id');

		var lpu_section_id = arguments[0].formParams.LpuSection_id;
		var med_personal_id = arguments[0].formParams.MedPersonal_id;

		base_form.clearInvalid();

		switch ( this.action ) {
			case 'add':
				this.setTitle(WND_POL_EVDT14ADD);
				this.enableEdit(true);

				base_form.findField('EvnVizitDispTeen14_IsSanKur').setValue(1);
				base_form.findField('EvnVizitDispTeen14_IsOut').setValue(1);
				base_form.findField('DopDispAlien_id').setValue(1);
				base_form.findField('LpuSection_id').setAllowBlank(false);
				base_form.findField('MedStaffFact_id').setAllowBlank(false);
				var diag_id = 10880;
				// ограничиваем годом пришедшим извне.
				this.setMinDate(this.action);
				
				diag_combo.getStore().load({
					callback: function() {
						diag_combo.setValue(diag_id);
						diag_combo.fireEvent('select', diag_combo, diag_combo.getStore().getAt(0), 0);
						diag_combo.setFilterByDate(base_form.findField('EvnVizitDispTeen14_setDate').getValue());
					},
					params: {
						where: "where DiagLevel_id = 4 and Diag_id = " + diag_id
					}
				});

				dop_disp_spec_combo.fireEvent('change', dop_disp_spec_combo, dop_disp_spec_combo.getValue());

				loadMask.hide();

				base_form.findField('EvnVizitDispTeen14_setDate').focus(false, 250);
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
							EvnVizitDispTeen14_id: arguments[0]['EvnVizitDispTeen14_id']
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
										diag_combo.setFilterByDate(base_form.findField('EvnVizitDispTeen14_setDate').getValue());
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
								this.setTitle(WND_POL_EVDT14EDIT);
								this.enableEdit(true);
								base_form.findField('EvnVizitDispTeen14_setDate').focus(false, 250);
							}
							else
							{
								this.setTitle(WND_POL_EVDT14VIEW);
								this.enableEdit(false);
								this.buttons[3].focus();
							}
						}.createDelegate(this),
						url: '/?c=EvnPLDispTeen14&m=loadEvnVizitDispTeen14EditForm'																		
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
								diag_combo.setFilterByDate(base_form.findField('EvnVizitDispTeen14_setDate').getValue());
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
						this.setTitle(WND_POL_EVDT14EDIT);
						this.enableEdit(true);
						base_form.findField('EvnVizitDispTeen14_setDate').focus(false, 250);
					}
					else
					{
						this.setTitle(WND_POL_EVDT14VIEW);
						this.enableEdit(false);
						this.buttons[3].focus();
					}					
				}
			break;
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
				this.findById('EVDT14EW_EvnVizitDispTeen14_setDate').setMinValue(Date.parseDate('01.01.' + this.Year, 'd.m.Y'));
			}
			else 
			{
				this.findById('EVDT14EW_EvnVizitDispTeen14_setDate').setMinValue(Date.parseDate('01.01.' + (Date.parseDate(getGlobalOptions().date, 'd.m.Y').getFullYear()-1), 'd.m.Y'));
			}
		}
		else 
		{
			var year = this.findById('EVDT14EW_EvnVizitDispTeen14_setDate').getValue().getFullYear();
			if (year)
			{
				this.findById('EVDT14EW_EvnVizitDispTeen14_setDate').setMinValue(Date.parseDate('01.01.' + year, 'd.m.Y'));
			}
		}
	},
	width: 700
});