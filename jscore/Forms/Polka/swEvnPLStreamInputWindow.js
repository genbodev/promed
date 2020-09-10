/**
* swEvnPLStreamInputWindow - окно потокового ввода талонов амбулаторного пациента.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Polka
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Stas Bykov aka Savage (savage@swan.perm.ru)
* @version      0.001-04.07.2009
* @comment      Префикс для id компонентов EPLSIF (EvnPLStreamInput)
*/

sw.Promed.swEvnPLStreamInputWindow = Ext.extend(sw.Promed.BaseForm, {
	begDate: null,
	begTime: null,
	buttonAlign: 'left',
	closable: true,
	closeAction: 'hide',
	formName: 'EvnPLStreamInputParams',
	collapsible: true,
	deleteEvnPL: function(options) {
		options = options || {};
		var grid = this.findById('EPLSIF_EvnPLGrid');

		if ( !grid || !grid.getGrid() ) {
			sw.swMsg.alert(lang['oshibka'], lang['pri_udalenii_talona_voznikli_oshibki_[tip_oshibki_1]']);
			return false;
		}
		else if ( !grid.getGrid().getSelectionModel().getSelected() ) {
			sw.swMsg.alert(lang['oshibka'], lang['ne_vyibran_talon_iz_spiska']);
			return false;
		}

		var selected_record = grid.getGrid().getSelectionModel().getSelected();
		var Evn_id = selected_record.get('EvnPL_id');

		if ( Ext.isEmpty(Evn_id) ) {
			return false;
		}

		var alert = {
			'701': {
				buttons: Ext.Msg.YESNO,
				fn: function(buttonId, scope, params) {
					if (buttonId == 'yes') {
						options.ignoreDoc = true;
						scope.deleteEvnPL(options);
					}
				}
			},
			'809': {
				buttons: Ext.Msg.YESNO,
				fn: function(buttonId, scope, params) {
					if (buttonId == 'yes') {
						Ext.Ajax.request({
							params: params,
							url: '/?c=HomeVisit&m=RevertHomeVizitStatusesTAP',
							callback: function(opts, success, response) {
								if (success) {
									var resp = Ext.util.JSON.decode(response.responseText);
									if (Ext.isEmpty(resp.Error_Msg)) {
										options.ignoreHomeVizit = true;
										scope.deleteEvnPL(options);
									} else {
										sw.swMsg.alert(langs('Ошибка'), resp.Error_Msg);
									}
								} else {
									sw.swMsg.alert(langs('Ошибка'), 'При измененении статусов вызовов на дом возникли ошибки');
								}
							}
						});
					}
				}
			}
		};

		var params = {Evn_id: Evn_id};

		if (options.ignoreDoc) {
			params.ignoreDoc = options.ignoreDoc;
		}

		if (options.ignoreHomeVizit) {
			params.ignoreHomeVizit = options.ignoreHomeVizit;
		}

		var doDelete = function() {
			Ext.Ajax.request({
				failure: function(response, options) {
					sw.swMsg.alert(lang['oshibka'], lang['pri_udalenii_talona_voznikli_oshibki_[tip_oshibki_2]']);
				},
				params: {
					Evn_id: Evn_id
				},
				success: function(response, options) {
					var response_obj = Ext.util.JSON.decode(response.responseText);

					if ( response_obj.success == false ) {
						if (response_obj.Alert_Msg) {
							var a_params = alert[response_obj.Alert_Code];
							sw.swMsg.show({
								buttons: a_params.buttons,
								fn: function(buttonId) {
									a_params.fn(buttonId, this, params);
								}.createDelegate(this),
								msg: response_obj.Alert_Msg,
								icon: Ext.MessageBox.QUESTION,
								title: lang['vopros']
							});
						} else {
							sw.swMsg.alert(lang['oshibka'], response_obj.Error_Msg ? response_obj.Error_Msg : lang['pri_udalenii_talona_voznikli_oshibki_[tip_oshibki_3]']);
						}
					}
					else {
						grid.getGrid().getStore().remove(selected_record);

						if (grid.getGrid().getStore().getCount() == 0) {
							grid.addEmptyRecord(grid.getGrid().getStore());
						}
					}

					grid.focus();
				},
				url: C_EVN_DEL
			});
		}.createDelegate(this);

		if (options.ignoreQuestion) {
			doDelete();
		} else {
			sw.swMsg.show({
				buttons: Ext.Msg.YESNO,
				fn: function(buttonId, text, obj) {
					if ( 'yes' == buttonId ) {
						options.ignoreQuestion = true;
						doDelete();
					}
				},
				icon: Ext.MessageBox.QUESTION,
				msg: lang['udalit_talon'],
				title: lang['vopros']
			});
		}
	},
	draggable: true,
	height: 550,
	id: 'EvnPLStreamInputWindow',
	printCost: function() {
		var grid = this.findById('EPLSIF_EvnPLGrid').getGrid();
		var selected_record = grid.getSelectionModel().getSelected();
		if (selected_record && selected_record.get('EvnPL_id')) {
			sw.Promed.CostPrint.print({
				Evn_id: selected_record.get('EvnPL_id'),
				type: 'EvnPL',
				callback: function() {
					grid.getStore().reload();
				}
			});
		}
	},
	initComponent: function() {
		var win = this;
		Ext.apply(this, {
			buttons: [{
				text: '-'
			},
			HelpButton(this),
			{
				handler: function() {
					this.hide();
				}.createDelegate(this),
				iconCls: 'close16',
				id: 'EPLSIF_CancelButton',
				onTabAction: function () {
					this.findById('EPLSIF_EvnVizitPL_setDate').focus(true, 100);
				}.createDelegate(this),
				onShiftTabAction: function () {
					this.focusOnGrid();
				}.createDelegate(this),
				tabIndex: TABINDEX_EPLSIF + 15,
				text: lang['zakryit']
			}],
			items: [{
				autoHeight: true,
				layout: 'form',
				region: 'north',
				items: [ new Ext.form.FormPanel({
					bodyStyle: 'padding: 5px',
					border: false,
					frame: false,
					id: 'EPLSIF_StreamInformationForm',
					items: [{
						disabled: true,
						fieldLabel: lang['polzovatel'],
						id: 'EPLSIF_pmUser_Name',
						width: 380,
						xtype: 'textfield'
					}, {
						disabled: true,
						fieldLabel: lang['data_nachala_vvoda'],
						id: 'EPLSIF_Stream_begDateTime',
						width: 130,
						xtype: 'textfield'
					}],
					labelAlign: 'right',
					labelWidth: 120
				}),
				new Ext.form.FormPanel({
					animCollapse: false,
					height: 230,
					bodyStyle: 'padding: 5px 5px 0',
					border: false,
					buttonAlign: 'left',
					// collapsible: true,
					frame: false,
					id: 'EvnPLStreamInputParams',
					items: [{
						border: false,
						layout: 'column',
						width: 800,
						items: [{
							border: false,
							layout: 'form',
							labelWidth: 130,
							items: [{
								fieldLabel: lang['data_posescheniya'],
								format: 'd.m.Y',
								id: 'EPLSIF_EvnVizitPL_setDate',
								listeners: {
									'change': function(field, newValue, oldValue) {
										var base_form = this.findById('EvnPLStreamInputParams').getForm();

										var index;
										var lpu_section_id = base_form.findField('LpuSection_id').getValue();
										var med_staff_fact_id = base_form.findField('MedStaffFact_id').getValue();
										var med_staff_fact_sid = base_form.findField('MedStaffFact_sid').getValue();
										var ResultClass_id = base_form.findField('ResultClass_id').getValue();
										var ServiceType_id = base_form.findField('ServiceType_id').getValue();

										// Фильтр на поле ResultClass_id
										// https://redmine.swan.perm.ru/issues/18184
										base_form.findField('ResultClass_id').clearValue();
										base_form.findField('ResultClass_id').getStore().clearFilter();
										base_form.findField('ResultClass_id').lastQuery = '';

										this.filterVizitTypeCombo();

										var xdate = new Date(2016, 0, 1);

										if ( !Ext.isEmpty(newValue) ) {
											if ( getRegionNick() == 'astra' ) {
												base_form.findField('ResultClass_id').getStore().filterBy(function(rec) {
													return (
														(Ext.isEmpty(rec.get('ResultClass_begDT')) || rec.get('ResultClass_begDT') <= newValue)
														&& (Ext.isEmpty(rec.get('ResultClass_endDT')) || rec.get('ResultClass_endDT') >= newValue)
														&& (Ext.isEmpty(rec.get('ResultClass_Code')) || rec.get('ResultClass_Code').inlist(['1','2','3','4','5']))
													);
												});
											}
											else {
												base_form.findField('ResultClass_id').getStore().filterBy(function(rec) {
													return (
														(Ext.isEmpty(rec.get('ResultClass_begDT')) || rec.get('ResultClass_begDT') <= newValue)
														&& (Ext.isEmpty(rec.get('ResultClass_endDT')) || rec.get('ResultClass_endDT') >= newValue)
														&& (!rec.get('ResultClass_Code') || !rec.get('ResultClass_Code').inlist(['6','7']) || getRegionNick() != 'perm' || newValue < xdate)
													);
												});
											}
										}
										else if ( getRegionNick() == 'astra' ) {
											base_form.findField('ResultClass_id').getStore().filterBy(function(rec) {
												return (
													(Ext.isEmpty(rec.get('ResultClass_Code')) || rec.get('ResultClass_Code').inlist(['1','2','3','4','5']))
												);
											});
										}

										if ( !Ext.isEmpty(ResultClass_id) ) {
											index = base_form.findField('ResultClass_id').getStore().findBy(function(rec) {
												return (rec.get('ResultClass_id') == ResultClass_id);
											});

											if ( index >= 0 ) {
												base_form.findField('ResultClass_id').setValue(ResultClass_id);
											}
										}

										// Фильтр на поле ServiceType_id
										// https://redmine.swan.perm.ru/issues/17571
										base_form.findField('ServiceType_id').clearValue();
										base_form.findField('ServiceType_id').getStore().clearFilter();
										base_form.findField('ServiceType_id').lastQuery = '';

										if ( !Ext.isEmpty(newValue) ) {
											base_form.findField('ServiceType_id').getStore().filterBy(function(rec) {
												return (
													(Ext.isEmpty(rec.get('ServiceType_begDate')) || rec.get('ServiceType_begDate') <= newValue)
													&& (Ext.isEmpty(rec.get('ServiceType_endDate')) || rec.get('ServiceType_endDate') >= newValue)
												);
											});
										}

										if ( !Ext.isEmpty(ServiceType_id) ) {
											index = base_form.findField('ServiceType_id').getStore().findBy(function(rec) {
												return (rec.get('ServiceType_id') == ServiceType_id);
											});

											if ( index >= 0 ) {
												base_form.findField('ServiceType_id').setValue(ServiceType_id);
											}
										}

										base_form.findField('LpuSection_id').clearValue();
										base_form.findField('MedStaffFact_id').clearValue();
										base_form.findField('MedStaffFact_sid').clearValue();

										var LpuSectionFilter = {
											allowLowLevel: 'yes',
											isPolka: true
										}

										var MedStaffFactFilter = {
											allowLowLevel: 'yes',
											EvnClass_SysNick: 'EvnVizit',
											isPolka: true/*,
											isDoctor: true*/
										}

										var MidMedStaffFactFilter = {
											isMidMedPersonalOnly: true,
											allowLowLevel: 'yes',
											isPolka: true
										}

										if ( !Ext.isEmpty(newValue) ) {
											LpuSectionFilter.onDate = Ext.util.Format.date(newValue, 'd.m.Y');
											MedStaffFactFilter.onDate = Ext.util.Format.date(newValue, 'd.m.Y');
											MidMedStaffFactFilter.onDate = Ext.util.Format.date(newValue, 'd.m.Y');
										}

										setLpuSectionGlobalStoreFilter(LpuSectionFilter);
										setMedStaffFactGlobalStoreFilter(MedStaffFactFilter);

										base_form.findField('LpuSection_id').getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));
										base_form.findField('MedStaffFact_id').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));

										setMedStaffFactGlobalStoreFilter(MidMedStaffFactFilter);

										base_form.findField('MedStaffFact_sid').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));


										if ( base_form.findField('LpuSection_id').getStore().getById(lpu_section_id) ) {
											base_form.findField('LpuSection_id').setValue(lpu_section_id);
										}

										if ( base_form.findField('MedStaffFact_id').getStore().getById(med_staff_fact_id) ) {
											base_form.findField('MedStaffFact_id').setValue(med_staff_fact_id);
										}

										if ( base_form.findField('MedStaffFact_sid').getStore().getById(med_staff_fact_sid) ) {
											base_form.findField('MedStaffFact_id').setValue(med_staff_fact_sid);
										}

										this.reloadUslugaComplexField();

									}.createDelegate(this),
									'keydown': function(inp, e) {
										if ( e.shiftKey == true && e.getKey() == Ext.EventObject.TAB ) {
											e.stopEvent();
											this.buttons[this.buttons.length - 1].focus();
										}
									}.createDelegate(this)
								},
								plugins: [ new Ext.ux.InputTextMask('99.99.9999', true) ],
								name: 'EvnVizitPL_setDate',
								tabIndex: TABINDEX_EPLSIF + 1,
								width: 100,
								xtype: 'swdatefield'
							}, {
								fieldLabel: lang['mesto_posescheniya'],
								id: 'EPLSIF_ServiceTypeCombo',
								listWidth: 300,
								tabIndex: TABINDEX_EPLSIF + 2,
								width: 200,
								xtype: 'swservicetypecombo'
							}, {
								id: 'EPLSIF_VizitTypeCombo',
								listWidth: 300,
								tabIndex: TABINDEX_EPLSIF + 3,
								width: 200,
								EvnClass_id: 11,
								xtype: 'swvizittypecombo'
							},{
								border: false,
								hidden: !(getRegionNick().inlist([ 'ufa', 'buryatiya' ])), // Открыто для Бурятии и Уфы
								layout: 'form',
								xtype: 'panel',
								items: [
									{
										fieldLabel: lang['kod_posescheniya'],
										hiddenName: 'UslugaComplex_uid',
										id: 'EPLSIF_UslugaComplex',
										listWidth: 500,
										tabIndex: TABINDEX_EVPLEF + 11,
										width: 200,
										xtype: 'swuslugacomplexnewcombo'
										
									}
								]
							},{ 
								layout: 'form',
								id: 'RskLevel',
								border:false,
								hidden:true,
								items: [{
								width: 183,
								xtype: 'swrisklevelcombo'}]
							},{
								id: 'EPLSIF_PayTypeCombo',
								listWidth: 300,
								tabIndex: TABINDEX_EPLSIF + 4,
								width: 200,
								useCommonFilter: true,
								xtype: 'swpaytypecombo',
								listeners: {
									'change': function(combo, newValue, oldValue) {
										this.filterVizitTypeCombo();
									}.createDelegate(this)
								}
							}, {
								fieldLabel: lang['sluchay_zakonchen'],
								hiddenName: 'EvnPL_IsFinish',
								id: 'EPLSIF_IsFinishCombo',
								listeners: {
									'change': function(combo, newValue, oldValue) {
										var index = combo.getStore().findBy(function(rec) {
											return (rec.get(combo.valueField) == newValue);
										});

										combo.fireEvent('select', combo, combo.getStore().getAt(index));

										return true;
									}.createDelegate(this),
									'select': function(combo, record, id) {
										var base_form = this.findById('EvnPLStreamInputParams').getForm();

										if ( !record || record.get('YesNo_Code') == 0 ) {
											base_form.findField('ResultClass_id').clearValue();

											if ( Ext.globalOptions.polka.is_finish_result_block == '1' ) {
												base_form.findField('ResultClass_id').disable();
											}
											else {
												base_form.findField('ResultClass_id').enable();
											}
										}
										else {
											base_form.findField('ResultClass_id').enable();
										}
									}.createDelegate(this)
								},
								tabIndex: TABINDEX_EPLSIF + 9,
								width: 70,
								xtype: 'swyesnocombo'
							}, {
								id: 'EPLSIF_DirectTypeCombo',
								listWidth: 300,
								tabIndex: TABINDEX_EPLSIF + 11,
								width: 200,
								xtype: 'swdirecttypecombo'
							}, {
								allowDecimals: true,
								allowNegative: false,
								fieldLabel: lang['ukl'],
								id: 'EPLSIF_EvnPL_UKL',
								maxValue: 1,
								name: 'EvnPL_UKL',
								tabIndex: TABINDEX_EPLSIF + 13,
								width: 70,
								value: 1,
								xtype: 'numberfield',
								enableKeyEvents: true,
								listeners:{
									'keydown':function (inp, e) {
										if (e.getKey() == Ext.EventObject.TAB) {
											if (!e.shiftKey) {
												e.stopEvent();
												win.focusOnGrid();
											}
										}
									}
								}

							}]
						}, {
							border: false,
							layout: 'form',
							labelWidth: 150,
							items: [{
								id: 'EPLSIF_LpuBuildingCombo',
								lastQuery: '',
								listWidth: 600,
								linkedElements: [
									'EPLSIF_LpuSectionCombo'
								],
								tabIndex: TABINDEX_EPLSIF + 5,
								width: 200,
								xtype: 'swlpubuildingglobalcombo'
							}, {
								id: 'EPLSIF_LpuSectionCombo',
								linkedElements: [
									'EPLSIF_MedPersonalCombo'
								],
								listWidth: 600,
								parentElementId: 'EPLSIF_LpuBuildingCombo',
								tabIndex: TABINDEX_EPLSIF + 6,
								width: 200,
								xtype: 'swlpusectionglobalcombo'
							}, {
								hiddenName: 'MedStaffFact_id',
								id: 'EPLSIF_MedPersonalCombo',
								parentElementId: 'EPLSIF_LpuSectionCombo',
								listWidth: 600,
								tabIndex: TABINDEX_EPLSIF + 7,
								width: 300,
								xtype: 'swmedstafffactglobalcombo'
							}, {
								fieldLabel: lang['sredniy_m_pers'],
								hiddenName: 'MedStaffFact_sid',
								id: 'EPLSIF_MedPersonalMidCombo',
								listWidth: 600,
								//parentElementId: 'EPLSIF_LpuSectionCombo',
								tabIndex: TABINDEX_EPLSIF + 8,
								width: 300,
								xtype: 'swmedstafffactglobalcombo'
							}, {
								id: 'EPLSIF_ResultClassCombo',
								tabIndex: TABINDEX_EPLSIF + 10,
								width: 200,
								xtype: 'swresultclasscombo'
							}, {
								id: 'EPLSIF_DirectClassCombo',
								tabIndex: TABINDEX_EPLSIF + 12,
								width: 200,
								xtype: 'swdirectclasscombo'
							}, {
								checkAccessRights: true,
								hiddenName: 'Diag_id',
								id: 'EPLSIF_DiagCombo',
								listWidth: 600,
								tabIndex: TABINDEX_EPLSIF + 14,
								width: 300,
								xtype: 'swdiagcombo'
							}, {
								border: false,
								hidden: getRegionNick() != 'kareliya',
								layout: 'form',
								xtype: 'panel',
								items: [{
									hiddenName: 'MedicalCareKind_id',
									id: 'EPLSIF_MedicalCareKindCombo',
									fieldLabel: lang['meditsinskaya_pomosch'],
									comboSubject: 'MedicalCareKind',
									xtype: 'swcommonsprcombo',
									width: 300
								}]
							}]
						}]
					}],
					labelAlign: 'right',
					//labelWidth: 130,
					title: lang['parametryi_vvoda']
				})]
			},
			new sw.Promed.ViewFrame({
				actions: [
					{name: 'action_add', handler: function() {this.openEvnPLEditWindow('add');}.createDelegate(this)},
					{name: 'action_edit', handler: function() {this.openEvnPLEditWindow('edit');}.createDelegate(this)},
					{name: 'action_view', handler: function() {this.openEvnPLEditWindow('view');}.createDelegate(this)},
					{name: 'action_delete', handler: function() {this.deleteEvnPL();}.createDelegate(this)},
					{name: 'action_refresh', handler: function() {this.refreshEvnPLGrid();}.createDelegate(this)},
					{name: 'action_print', menuConfig: {
						printObject: { text: langs('Печать ТАП'), handler: function() { this.printEvnPL();}.createDelegate(this) },
						printCost: {name: 'printCost', hidden: ! getRegionNick().inlist(['perm', 'kz', 'ufa']), text: langs('Справка о стоимости лечения'), handler: function () { win.printCost() }}
					}}
				],
				autoExpandColumn: 'autoexpand',
				autoExpandMin: 150,
				autoLoadData: false,
				dataUrl: '/?c=EvnPL&m=loadEvnPLStreamList',
				focusOn: {
					name: 'EPLSIF_CancelButton',
					type: 'button'
				},
				focusPrev: {
					name: 'EPLSIF_EvnPL_UKL',
					type: 'field'
				},
				id: 'EPLSIF_EvnPLGrid',
				pageSize: 100,
				paging: false,
				region: 'center',
				root: 'data',
				stringfields: [
					{name: 'EvnPL_id', type: 'int', header: 'ID', key: true},
					{name: 'Person_id', type: 'int', hidden: true},
					{name: 'PersonEvn_id', type: 'int', hidden: true},
					{name: 'Server_id', type: 'int', hidden: true},
					{name: 'EvnPL_NumCard', type: 'string', header: langs('№ талона'), width: 70},
					{name: 'Person_Surname', type: 'string', header: langs('Фамилия'), id: 'autoexpand'},
					{name: 'Person_Firname', type: 'string', header: langs('Имя'), width: 150},
					{name: 'Person_Secname', type: 'string', header: langs('Отчество'), width: 150},
					{name: 'Person_Birthday', type: 'date', format: 'd.m.Y', header: langs('Д/р'), width: 100},
					{name: 'EvnPL_setDate', type: 'date', format: 'd.m.Y', header: langs('Дата начала'), width: 100},
					{name: 'EvnPL_disDate', type: 'date', format: 'd.m.Y', header: langs('Дата окончания'), width: 100},
					{name: 'EvnPL_VizitCount', type: 'int', header: langs('Посещений'), width: 100},
					{name: 'EvnPL_IsFinish', type: 'string', header: langs('Законч'), width: 100},
					{ name: 'EvnCostPrint_setDT', type: 'date', header: langs('Дата выдачи справки/отказа'), width: 150, hidden: ! getRegionNick().inlist(['perm', 'kz', 'ufa']) },
					{ name: 'EvnCostPrint_IsNoPrintText', type: 'string', header: langs('Справка о стоимости лечения'), width: 150, hidden: ! getRegionNick().inlist(['perm', 'kz', 'ufa']) }
				],
				toolbar: true,
				totalProperty: 'totalCount'
			})]
		});
		sw.Promed.swEvnPLStreamInputWindow.superclass.initComponent.apply(this, arguments);
		if(getRegionNick().inlist([ 'ufa', 'buryatiya'])){
			this.findById('EPLSIF_LpuSectionCombo').addListener('change', function(combo, newValue, oldValue) {

				var base_form = this.findById('EvnPLStreamInputParams').getForm(),
					uslugacomplex_combo = base_form.findField('UslugaComplex_uid'),
					LpuSectionProfile_Code = combo.getFieldValue('LpuSectionProfile_Code'),
					usluga_complex_id = uslugacomplex_combo.getValue();

				if ( getRegionNick() == 'ufa' && !newValue ) {
					uslugacomplex_combo.setLpuLevelCode(0);
					return false;
				}

				if (!Ext.isEmpty(LpuSectionProfile_Code)){
					base_form.findField('UslugaComplex_uid').setLpuLevelCode(LpuSectionProfile_Code);
				}


				var index = combo.getStore().findBy(function(rec) {
					return rec.get('LpuSection_id') == newValue;
				});
				var record = combo.getStore().getAt(index);

				if ( record ) {
					if ( getRegionNick() == 'ufa' ) {
						uslugacomplex_combo.setLpuLevelCode(record.get('LpuSectionProfile_Code'));
					} else if ( getRegionNick().inlist(['buryatiya', 'perm']) ) {
						uslugacomplex_combo.setLpuSectionProfile_id(record.get('LpuSectionProfile_id'));
					}

					this.reloadUslugaComplexField();
				}
			}.createDelegate(this));

			this.findById('EPLSIF_MedPersonalCombo').addListener('change', function(combo, newValue, oldValue) {
				var base_form = this.findById('EvnPLStreamInputParams').getForm();
				var uslugacomplex_combo = base_form.findField('UslugaComplex_uid');
				var usluga_complex_id = uslugacomplex_combo.getValue();

				if ( getRegionNick().inlist(['ufa']) ) {

					if ( !newValue ) {
						uslugacomplex_combo.setLpuLevelCode(0);
						return false;
					}

					var index = combo.getStore().findBy(function(rec) {
						return rec.get('MedStaffFact_id') == newValue;
					});
					var record = combo.getStore().getAt(index);

					this.reloadUslugaComplexField();

					if ( record ) {
						uslugacomplex_combo.setLpuLevelCode(record.get('LpuSectionProfile_Code'));
					}

				} else if ( getRegionNick().inlist(['buryatiya','perm']) ) {
					uslugacomplex_combo.setLpuSectionProfile_id(base_form.findField('LpuSection_id').getFieldValue('LpuSectionProfile_id'));
					this.reloadUslugaComplexField();
				}

			}.createDelegate(this));
		}
		//focusing viewframes
		var grid = this.findById('EPLSIF_EvnPLGrid');
		grid.focusPrev = this.findById('EPLSIF_EvnPL_UKL');
		grid.focusPrev.type = 'field';
		grid.focusPrev.name = grid.focusPrev.id;
		grid.focusOn = this.buttons[2];
		grid.focusOn.type = 'field';
		grid.focusOn.name = grid.focusOn.id;

		this.focusOnGrid = function () {
			var grid = win.findById('EPLSIF_EvnPLGrid').getGrid();
			if (grid.getStore().getCount() > 0) {
				grid.getView().focusRow(0);
				grid.getSelectionModel().selectFirstRow();
			}
		}

	},
	reloadUslugaComplexField: function(needUslugaComplex_id, wantUslugaComplex_id) {
		if ( !getRegionNick().inlist([ 'buryatiya', 'ufa' ]) ) {
			return false;
		}

		var win = this;
		if (win.blockUslugaComplexReload) {
			return false;
		}

		var base_form = this.findById('EvnPLStreamInputParams').getForm();
		var field = base_form.findField('UslugaComplex_uid');

		/*if (getRegionNick() == 'perm') {
		 field.getStore().baseParams.VizitType_id = base_form.findField('VizitType_id').getValue();
		 field.getStore().baseParams.VizitClass_id = base_form.findField('VizitClass_id').getValue();
		 }*/

		if ( getRegionNick().inlist(['buryatiya', 'perm']) ) {
			field.getStore().baseParams.LpuSectionProfile_id = base_form.findField('LpuSection_id').getFieldValue('LpuSectionProfile_id');
		}

		field.getStore().baseParams.UslugaComplex_Date = Ext.util.Format.date(base_form.findField('EvnVizitPL_setDate').getValue(), 'd.m.Y');
		//field.getStore().baseParams.EvnVizit_id = base_form.findField('EvnVizitPL_id').getValue();
		field.getStore().baseParams.query = "";

		// повторно грузить одно и то же не нужно
		var newUslugaComplexParams = Ext.util.JSON.encode(field.getStore().baseParams);
		if (needUslugaComplex_id || newUslugaComplexParams != win.lastUslugaComplexParams) {
			win.lastUslugaComplexParams = newUslugaComplexParams;
			var currentUslugaComplex_id = base_form.findField('UslugaComplex_uid').getValue();
			field.lastQuery = 'This query sample that is not will never appear';
			field.getStore().removeAll();

			var params = {};
			if (needUslugaComplex_id) {
				params.UslugaComplex_id = needUslugaComplex_id;
				currentUslugaComplex_id = needUslugaComplex_id;
			}

			field.getStore().load({
				callback: function (rec) {
					var index = -1;
					if (wantUslugaComplex_id) {
						index = base_form.findField('UslugaComplex_uid').getStore().findBy(function (rec) {
							return (rec.get('UslugaComplex_id') == wantUslugaComplex_id);
						});
					}
					if (index < 0) {
						index = base_form.findField('UslugaComplex_uid').getStore().findBy(function (rec) {
							return (rec.get('UslugaComplex_id') == currentUslugaComplex_id);
						});
					}
					if (index < 0 && getRegionNick() == 'pskov' && base_form.findField('UslugaComplex_uid').getStore().getCount() == 1) {
						index = 0;
					}

					if (index >= 0) {
						var record = base_form.findField('UslugaComplex_uid').getStore().getAt(index);
						field.setValue(record.get('UslugaComplex_id'));
						field.setRawValue(record.get('UslugaComplex_Code') + '. ' + record.get('UslugaComplex_Name'));
					} else {
						field.clearValue();
					}
				},
				params: params
			});
		} else if (wantUslugaComplex_id) {
			var index = base_form.findField('UslugaComplex_uid').getStore().findBy(function (rec) {
				return (rec.get('UslugaComplex_id') == wantUslugaComplex_id);
			});
			if (index >= 0) {
				var record = base_form.findField('UslugaComplex_uid').getStore().getAt(index);
				field.setValue(record.get('UslugaComplex_id'));
				field.setRawValue(record.get('UslugaComplex_Code') + '. ' + record.get('UslugaComplex_Name'));
			} else {
				field.clearValue();
			}
		}
	},
	keys: [{
		fn: function(inp, e) {
			Ext.getCmp('EvnPLStreamInputWindow').openEvnPLEditWindow('add');
		},
		key: [
			Ext.EventObject.INSERT
		],
		stopEvent: true
	}, {
		alt: true,
		fn: function(inp, e) {
			Ext.getCmp('EvnPLStreamInputWindow').hide();
		},
		key: [
			Ext.EventObject.P
		],
		stopEvent: true
	}],
	layout: 'border',
	maximizable: true,
	minHeight: 550,
	minWidth: 800,
	modal: true,
	openEvnPLEditWindow: function(action) {
		if ( action != 'add' && action != 'edit' && action != 'view' ) {
			return false;
		}

		if ( action == 'add' && getWnd('swPersonSearchWindow').isVisible() ) {
			sw.swMsg.alert(lang['soobschenie'], lang['okno_poiska_cheloveka_uje_otkryito']);
			return false;
		}

		if ( getWnd('swEvnPLEditWindow').isVisible() ) {
			sw.swMsg.alert(lang['soobschenie'], lang['okno_redaktirovaniya_talona_ambulatornogo_patsienta_uje_otkryito']);
			return false;
		}

		var grid = this.findById('EPLSIF_EvnPLGrid').getGrid();

		if ( !grid ) {
			sw.swMsg.alert(lang['oshibka'], lang['ne_udalos_nayti_spisok_talonov']);
			return false;
		}

		var index;
		var params = new Object();
		var base_form =this.findById('EvnPLStreamInputParams').getForm();
		params.action = action;
		params.callback = function(data) {
			if ( !data || !data.evnPLData ) {
				return false;
			}

			var record = grid.getStore().getById(data.evnPLData.EvnPL_id);

			if ( !record ) {
				if ( grid.getStore().getCount() == 1 && !grid.getStore().getAt(0).get('EvnPL_id') ) {
					grid.getStore().removeAll();
				}

				grid.getStore().loadData({'data': [ data.evnPLData ]}, true);
			}
			else {
				var evn_pl_fields = new Array();

				grid.getStore().fields.eachKey(function(key, item) {
					evn_pl_fields.push(key);
				});

				for ( i = 0; i < evn_pl_fields.length; i++ ) {
					record.set(evn_pl_fields[i], data.evnPLData[evn_pl_fields[i]]);
				}

				record.commit();
			}
		}.createDelegate(this);
		params.streamInput = true;

		if ( action == 'add' ) {
			params.Diag_id = this.findById('EPLSIF_DiagCombo').getValue();
			params.DirectClass_id = this.findById('EPLSIF_DirectClassCombo').getValue();
			params.DirectType_id = this.findById('EPLSIF_DirectTypeCombo').getValue();
			params.EvnPL_IsFinish = this.findById('EPLSIF_IsFinishCombo').getValue();
			params.EvnPL_UKL = this.findById('EPLSIF_EvnPL_UKL').getValue();
			params.EvnVizitPL_setDate = this.findById('EPLSIF_EvnVizitPL_setDate').getValue();
			params.UslugaComplex_uid = this.findById('EPLSIF_UslugaComplex').getValue();
			params.LpuSection_id = this.findById('EPLSIF_LpuSectionCombo').getValue();
			params.MedStaffFact_id = this.findById('EPLSIF_MedPersonalCombo').getValue();
			params.MedStaffFact_sid = this.findById('EPLSIF_MedPersonalMidCombo').getValue();
			params.MedPersonal_id = null;
			params.MedPersonal_sid = null;
			params.onHide = function() {
				getWnd('swPersonSearchWindow').FilterPanel.getForm().findField('PersonSurName_SurName').focus(true, 500);
			};
			params.PayType_id = this.findById('EPLSIF_PayTypeCombo').getValue();
			params.ResultClass_id = this.findById('EPLSIF_ResultClassCombo').getValue();
			params.ServiceType_id = this.findById('EPLSIF_ServiceTypeCombo').getValue();
			params.VizitType_id = this.findById('EPLSIF_VizitTypeCombo').getValue();
			params.MedicalCareKind_id = this.findById('EPLSIF_MedicalCareKindCombo').getValue();
			params.RiskLevel_id = (base_form.findField('RiskLevel_id'))?base_form.findField('RiskLevel_id').getValue():null;
			index = this.findById('EPLSIF_MedPersonalCombo').getStore().findBy(function(rec) {
				return (rec.get('MedStaffFact_id') == this.findById('EPLSIF_MedPersonalCombo').getValue());
			}.createDelegate(this));

			if ( index >= 0 ) {
				params.MedPersonal_id = this.findById('EPLSIF_MedPersonalCombo').getStore().getAt(index).get('MedPersonal_id');
			}

			index = this.findById('EPLSIF_MedPersonalMidCombo').getStore().findBy(function(rec) {
				return (rec.get('MedStaffFact_id') == this.findById('EPLSIF_MedPersonalMidCombo').getValue());
			}.createDelegate(this));

			if ( index >= 0 ) {
				params.MedPersonal_sid = this.findById('EPLSIF_MedPersonalMidCombo').getStore().getAt(index).get('MedPersonal_id');
			}

			getWnd('swPersonSearchWindow').show({
				onClose: function() {
					if ( grid.getSelectionModel().getSelected() ) {
						grid.getView().focusRow(grid.getStore().indexOf(grid.getSelectionModel().getSelected()));
					}
					else {
						grid.getView().focusRow(0);
						grid.getSelectionModel().selectFirstRow();
					}
				}.createDelegate(this),
				onSelect: function(person_data) {
					params.Person_id = person_data.Person_id;
					params.PersonEvn_id = person_data.PersonEvn_id;
					params.Server_id = person_data.Server_id;

					getWnd('swEvnPLEditWindow').show(params);
				},
				searchMode: 'all'
			});
		}
		else {
			if ( !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('EvnPL_id') ) {
				sw.swMsg.alert(lang['oshibka'], lang['ne_vyibran_talon_iz_spiska']);
				return false;
			}

			var record = grid.getSelectionModel().getSelected();

			params.onPersonChange = function(data) {
				if (data.Evn_id) {
					record.set('EvnPL_id', data.Evn_id);
					record.set('PersonEvn_id', data.PersonEvn_id);
					record.set('Person_id', data.Person_id);
					record.set('Server_id', data.Server_id);
					record.set('Person_Surname', data.Person_SurName);
					record.set('Person_Firname', data.Person_FirName);
					record.set('Person_Secname', data.Person_SecName);
					record.commit();
				}
			};

			params.EvnPL_id = record.get('EvnPL_id');
			params.onHide = function() {
				grid.getView().focusRow(grid.getStore().indexOf(record));
				grid.getSelectionModel().selectRow(grid.getStore().indexOf(record));
			};
			params.Person_id = record.get('Person_id');
			params.PersonEvn_id = record.get('PersonEvn_id');
			params.Server_id = record.get('Server_id');

			getWnd('swEvnPLEditWindow').show(params);
		}
	},
	plain: true,
	pmUser_Name: null,
	printEvnPL: function() {
		var grid = this.findById('EPLSIF_EvnPLGrid').ViewGridPanel;

		if ( !grid.getSelectionModel().getSelected() ) {
			return false;
		}

		var evn_pl_id = grid.getSelectionModel().getSelected().get('EvnPL_id');

		printEvnPL({
			type: 'EvnPL',
			EvnPL_id: evn_pl_id
		});
	},
	refreshEvnPLGrid: function() {
		var grid = this.findById('EPLSIF_EvnPLGrid').getGrid();

		grid.getSelectionModel().clearSelections();
		grid.getStore().reload();

		if ( grid.getStore().getCount() > 0 ) {
			grid.getView().focusRow(0);
			grid.getSelectionModel().selectFirstRow();
		}
	},
	resizable: false,
	setRiskComboState:function(val){
		var base_form = this.findById('EvnPLStreamInputParams').getForm();
		if(val=='cz'){			
			this.findById('RskLevel').show();
			base_form.findField('RiskLevel_id').enable();
			base_form.findField('RiskLevel_id').getStore().load();
			base_form.findField('RiskLevel_id').setAllowBlank(false);



		}else{
			//base_form.findField('RiskLevel_id').setValue('');
			base_form.findField('RiskLevel_id').setAllowBlank(true);
			this.findById('RskLevel').hide();
			base_form.findField('RiskLevel_id').disable()
		}	
	},
	setBegDateTime: function() {
		Ext.Ajax.request({
			callback: function(opt, success, response) {
				if ( success && response.responseText != '' ) {
					var response_obj = Ext.util.JSON.decode(response.responseText);

					this.begDate = response_obj.begDate;
					this.begTime = response_obj.begTime;

					this.findById('EPLSIF_StreamInformationForm').findById('EPLSIF_pmUser_Name').setValue(response_obj.pmUser_Name);
					this.findById('EPLSIF_StreamInformationForm').findById('EPLSIF_Stream_begDateTime').setValue(response_obj.begDate + ' ' + response_obj.begTime);
					this.findById('EPLSIF_EvnPLGrid').getGrid().getStore().baseParams.begDate = response_obj.begDate;
					this.findById('EPLSIF_EvnPLGrid').getGrid().getStore().baseParams.begTime = response_obj.begTime;
					this.findById('EPLSIF_UslugaComplex').getStore().baseParams.UslugaComplex_Date = response_obj.begDate;
				}
			}.createDelegate(this),
			url: C_LOAD_CURTIME
		});
	},
	filterVizitTypeCombo: function() {
		var base_form = this.findById('EvnPLStreamInputParams').getForm();
		var formDate = base_form.findField('EvnVizitPL_setDate').getValue();
		var pay_type_nick = base_form.findField('PayType_id').getFieldValue('PayType_SysNick');
		if (getRegionNick() == 'kareliya') {
			if (Ext.isEmpty(pay_type_nick) || pay_type_nick == 'oms') {
				var denied_visit_type_codes = ['41', '51', '2.4', '3.1'];
				if (formDate < new Date('2019-05-01')) {
					denied_visit_type_codes.push('1.2');
				}
				base_form.findField('VizitType_id').setFilterByDateAndCode(formDate, denied_visit_type_codes);
			} else {
				base_form.findField('VizitType_id').setFilterByDate(formDate);
			}
		} else {
			base_form.findField('VizitType_id').setFilterByDate(formDate);
		}
	},
	show: function() {
		sw.Promed.swEvnPLStreamInputWindow.superclass.show.apply(this, arguments);

		this.restore();
		this.center();
		this.maximize();

		this.begDate = null;
		this.begTime = null;
		this.pmUser_Name = null;

		var base_form = this.findById('EvnPLStreamInputParams').getForm();
		base_form.reset();
		base_form.findField('RiskLevel_id').setValue('');
		//base_form.findField('Diag_id').disable();

		swLpuBuildingGlobalStore.clearFilter();
		base_form.findField('LpuBuilding_id').getStore().loadData(getStoreRecords(swLpuBuildingGlobalStore));

		base_form.findField('EvnVizitPL_setDate').fireEvent('change', base_form.findField('EvnVizitPL_setDate'), '');

		if (getGlobalOptions().region && getGlobalOptions().region.nick == 'kareliya') {
			base_form.findField('MedicalCareKind_id').setFieldValue('MedicalCareKind_Code', 1);
		}

		base_form.findField('UslugaComplex_uid').getStore().baseParams.allowMorbusVizitOnly = 0;
		base_form.findField('UslugaComplex_uid').getStore().baseParams.allowNonMorbusVizitOnly = 0;
		base_form.findField('UslugaComplex_uid').getStore().baseParams.allowedUslugaComplexAttributeMethod = 'or';

		switch ( getRegionNick() ) {
			case 'ufa':
				base_form.findField('UslugaComplex_uid').setUslugaCategoryList([ 'lpusection' ]);
				break;
			case 'buryatiya':
				base_form.findField('UslugaComplex_uid').setUslugaCategoryList([ 'tfoms' ]);
				base_form.findField('UslugaComplex_uid').setAllowedUslugaComplexAttributeList([ 'vizit' ]);
				base_form.findField('UslugaComplex_uid').getStore().baseParams.isVizitCode = 1;
				base_form.findField('UslugaComplex_uid').getStore().baseParams.allowMorbusVizitCodesGroup88 = 0;
				base_form.findField('UslugaComplex_uid').getStore().baseParams.ignoreUslugaComplexDate = 0;
				break;
		}

		// Заполнение полей "Пользователь" и "Дата начала ввода"
		this.setBegDateTime();
		var grid = this.findById('EPLSIF_EvnPLGrid');
		grid.removeAll();
		setTimeout(this.focusOnGrid, 500);
	},
	title: WND_POL_EPLSTIN,
	width: 800
});