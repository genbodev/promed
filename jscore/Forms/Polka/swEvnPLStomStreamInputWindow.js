/**
* swEvnPLStomStreamInputWindow - окно потокового ввода талонов амбулаторного пациента для стоматологии.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Polka
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Stas Bykov aka Savage (savage@swan.perm.ru)
* @version      0.001-01.02.2010
* @comment      Префикс для id компонентов EPLSSIF (EvnPLStomStreamInput)
*/

sw.Promed.swEvnPLStomStreamInputWindow = Ext.extend(sw.Promed.BaseForm, {
	begDate: null,
	begTime: null,
	buttonAlign: 'left',
	closable: true,
	closeAction: 'hide',
	collapsible: true,
	deleteEvnPLStom: function(options) {
		options = options || {};
		var grid = this.findById('EPLSSIF_EvnPLStomGrid');

		if ( !grid || !grid.getGrid() ) {
			sw.swMsg.alert(lang['oshibka'], lang['pri_udalenii_talona_voznikli_oshibki_[tip_oshibki_1]']);
			return false;
		}
		else if ( !grid.getGrid().getSelectionModel().getSelected() ) {
			sw.swMsg.alert(lang['oshibka'], lang['ne_vyibran_talon_iz_spiska']);
			return false;
		}

		var selected_record = grid.getGrid().getSelectionModel().getSelected();
		var Evn_id = selected_record.get('EvnPLStom_id');

		if ( Ext.isEmpty(Evn_id) ) {
			return false;
		}

		var alert = {
			'701': {
				buttons: Ext.Msg.YESNO,
				fn: function(buttonId, scope, params) {
					if (buttonId == 'yes') {
						options.ignoreDoc = true;
						scope.deleteEvnPLStom(options);
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
							callback: function (opts, success, response) {
								if (success) {
									var resp = Ext.util.JSON.decode(response.responseText);
									if (Ext.isEmpty(resp.Error_Msg)) {
										options.ignoreHomeVizit = true;
										scope.deleteEvnPLStom(options);
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
				params: params,
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
					if ( buttonId == 'yes' ) {
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
	id: 'EvnPLStomStreamInputWindow',
	printCost: function() {
		var grid = this.findById('EPLSSIF_EvnPLStomGrid').getGrid();
		var selected_record = grid.getSelectionModel().getSelected();
		if (selected_record && selected_record.get('EvnPLStom_id')) {
			sw.Promed.CostPrint.print({
				Evn_id: selected_record.get('EvnPLStom_id'),
				type: 'EvnPLStom',
				callback: function() {
					grid.getStore().reload();
				}
			});
		}
	},
	initComponent: function() {
		var win = this;

		var EPLSSIF_EvnPLStomGrid = new sw.Promed.ViewFrame({
			actions: [
				{ name: 'action_add', handler: function() { this.openEvnPLStomEditWindow('add'); }.createDelegate(this) },
				{ name: 'action_edit', handler: function() { this.openEvnPLStomEditWindow('edit'); }.createDelegate(this) },
				{ name: 'action_view', handler: function() { this.openEvnPLStomEditWindow('view'); }.createDelegate(this) },
				{ name: 'action_delete', handler: function() { this.deleteEvnPLStom(); }.createDelegate(this) },
				{ name: 'action_refresh', handler: function() { this.refreshEvnPLStomGrid(); }.createDelegate(this) },
				{name: 'action_print', menuConfig: {
					printObject: { text: langs('Печать ТАП'), handler: function() { this.printEvnPLStom();}.createDelegate(this) },
					printCost: {name: 'printCost', hidden: ! getRegionNick().inlist(['perm', 'kz', 'ufa']), text: langs('Справка о стоимости лечения'), handler: function () { win.printCost() }}
				}}
			],
			autoExpandColumn: 'autoexpand',
			autoExpandMin: 150,
			autoLoadData: false,
			dataUrl: '/?c=EvnPLStom&m=loadEvnPLStomStreamList',
			id: 'EPLSSIF_EvnPLStomGrid',
			pageSize: 100,
			paging: false,
			region: 'center',
			root: 'data',
			stringfields: [
				{ name: 'EvnPLStom_id', type: 'int', header: 'ID', key: true },
				{ name: 'Person_id', type: 'int', hidden: true },
				{ name: 'PersonEvn_id', type: 'int', hidden: true },
				{ name: 'Server_id', type: 'int', hidden: true },
				{ name: 'EvnPLStom_NumCard', type: 'string', header: langs('№ талона'), width: 70 },
				{ name: 'Person_Surname', type: 'string', header: langs('Фамилия'), id: 'autoexpand' },
				{ name: 'Person_Firname', type: 'string', header: langs('Имя'), width: 150 },
				{ name: 'Person_Secname', type: 'string', header: langs('Отчество'), width: 150 },
				{ name: 'Person_Birthday', type: 'date', format: 'd.m.Y', header: langs('Д/р'), width: 100 },
				{ name: 'EvnPLStom_setDate', type: 'date', format: 'd.m.Y', header: langs('Дата начала'), width: 100 },
				{ name: 'EvnPLStom_disDate', type: 'date', format: 'd.m.Y', header: langs('Дата окончания'), width: 100 },
				{ name: 'EvnPLStom_VizitCount', type: 'int', header: langs('Посещений'), width: 100 },
				{ name: 'EvnPLStom_IsFinish', type: 'string', header: langs('Законч'), width: 100 },
				{ name: 'EvnCostPrint_setDT', type: 'date', header: langs('Дата выдачи справки/отказа'), width: 150, hidden: ! getRegionNick().inlist(['perm', 'kz', 'ufa']) },
				{ name: 'EvnCostPrint_IsNoPrintText', type: 'string', header: langs('Справка о стоимости лечения'), width: 150, hidden: ! getRegionNick().inlist(['perm', 'kz', 'ufa']) }
			],
			toolbar: true,
			totalProperty: 'totalCount'
		});
		var that = this;
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
				id: 'EPLSSIF_CancelButton',
				onTabAction: function () {
					this.findById('EPLSSIF_EvnVizitPLStom_setDate').focus(true, 100);
				}.createDelegate(this),
				onShiftTabAction: function () {
					var grid = this.findById('EPLSSIF_EvnPLStomGrid').getGrid();
					if (grid.getStore().getCount() > 0) {
						grid.getView().focusRow(0);
						grid.getSelectionModel().selectFirstRow();
					}
				}.createDelegate(this),
				tabIndex: TABINDEX_EPLSSIF + 14,
				text: lang['zakryit']
			}],
			items: [
				{
				autoHeight: true,
				layout: 'form',
				region: 'north',
				items: [ new Ext.form.FormPanel({
					bodyStyle: 'padding: 5px',
					border: false,
					frame: false,
					id: 'EPLSSIF_StreamInformationForm',
					items: [{
						disabled: true,
						fieldLabel: lang['polzovatel'],
						id: 'EPLSSIF_pmUser_Name',
						width: 380,
						xtype: 'textfield'
					}, {
						disabled: true,
						fieldLabel: lang['data_nachala_vvoda'],
						id: 'EPLSSIF_Stream_begDateTime',
						width: 130,
						xtype: 'textfield'
					}],
					labelAlign: 'right',
					labelWidth: 120
				}),
				new Ext.form.FormPanel({
					animCollapse: false,
					autoHeight: true,
					bodyStyle: 'padding: 5px 5px 0',
					border: false,
					buttonAlign: 'left',
					// collapsible: true,
					frame: false,
					id: 'EvnPLStomStreamInputParams',
					items: [{
						border: false,
						layout: 'column',
						width: 800,
						items: [{
							border: false,
							labelAlign: 'right',
							layout: 'form',
							labelWidth: 130,
							items: [{
								fieldLabel: lang['data_posescheniya'],
								format: 'd.m.Y',
								id: 'EPLSSIF_EvnVizitPLStom_setDate',
								listeners: {
									'change': function(field, newValue, oldValue) {
										var base_form = this.findById('EvnPLStomStreamInputParams').getForm();

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

										if ( !Ext.isEmpty(newValue) ) {
											base_form.findField('ResultClass_id').getStore().filterBy(function(rec) {
												return (
													(Ext.isEmpty(rec.get('ResultClass_begDT')) || rec.get('ResultClass_begDT') <= newValue)
													&& (Ext.isEmpty(rec.get('ResultClass_endDT')) || rec.get('ResultClass_endDT') >= newValue)
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

										index = base_form.findField('ServiceType_id').getStore().findBy(function(rec) {
											return (rec.get('ServiceType_id') == ServiceType_id);
										});

										if ( index >= 0 ) {
											base_form.findField('ServiceType_id').setValue(ServiceType_id);
										}

										base_form.findField('LpuSection_id').clearValue();
										base_form.findField('MedStaffFact_id').clearValue();
										base_form.findField('MedStaffFact_sid').clearValue();

										var lpu_section_filter_params = {
											isStom: true,
											regionCode: getGlobalOptions().region.number
										};

										var medstafffact_filter_params = {
											EvnClass_SysNick: 'EvnVizit',
											//isDoctor: true, // Только врачи
											isStom: true,
											regionCode: getGlobalOptions().region.number
										};

										var mid_medstafffact_filter_params = {
											isMidMedPersonal: true, // Средний мед. персонал + зубные врачи
											isStom: true,
											regionCode: getGlobalOptions().region.number
										};

										if ( !Ext.isEmpty(newValue) ) {
											lpu_section_filter_params.onDate = Ext.util.Format.date(newValue, 'd.m.Y');
											medstafffact_filter_params.onDate = Ext.util.Format.date(newValue, 'd.m.Y');
											mid_medstafffact_filter_params.onDate = Ext.util.Format.date(newValue, 'd.m.Y');
										}

										// Фильтруем список отделений и врачей
										setLpuSectionGlobalStoreFilter(lpu_section_filter_params);
										setMedStaffFactGlobalStoreFilter(medstafffact_filter_params);

										base_form.findField('LpuSection_id').getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));
										base_form.findField('MedStaffFact_id').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));

										// Фильтруем список среднего мед. персонала
										setMedStaffFactGlobalStoreFilter(mid_medstafffact_filter_params);

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
									}.createDelegate(this),
									'keydown': function(inp, e) {
										if ( e.shiftKey == true && e.getKey() == Ext.EventObject.TAB ) {
											e.stopEvent();
											this.buttons[this.buttons.length - 1].focus();
										}
									}.createDelegate(this)
								},
								plugins: [ new Ext.ux.InputTextMask('99.99.9999', true) ],
								name: 'EvnVizitPLStom_setDate',
								tabIndex: TABINDEX_EPLSSIF + 1,
								width: 100,
								xtype: 'swdatefield'
							}, {
								fieldLabel: lang['mesto_posescheniya'],
								id: 'EPLSSIF_ServiceTypeCombo',
								listWidth: 300,
								tabIndex: TABINDEX_EPLSSIF + 2,
								width: 200,
								xtype: 'swservicetypecombo'
							}, {
								border: false,
								hidden: !(getGlobalOptions().region && getGlobalOptions().region.nick== 'ufa'),
								layout: 'form',
								xtype: 'panel',
								items: [
									{
										fieldLabel: lang['kod_posescheniya'],
										hiddenName: 'UslugaComplex_uid',
										id: 'EPLSSIF_UslugaComplex',
										listWidth: 300,
										tabIndex: TABINDEX_EVPLEF + 11,
										width: 200,
										xtype: 'swuslugacomplexnewcombo'
									}
								]
							}, {
								id: 'EPLSSIF_VizitTypeCombo',
								listWidth: 300,
								tabIndex: TABINDEX_EPLSSIF + 3,
								width: 200,
								EvnClass_id: 13,
								xtype: 'swvizittypecombo'
							}, {
								// disabled: true,
								id: 'EPLSSIF_PayTypeCombo',
								listWidth: 300,
								tabIndex: TABINDEX_EPLSSIF + 4,
								width: 200,
								useCommonFilter: true,
								xtype: 'swpaytypecombo'
							}, {
								fieldLabel: lang['sluchay_zakonchen'],
								hiddenName: 'EvnPLStom_IsFinish',
								id: 'EPLSSIF_IsFinishCombo',
								listeners: {
									'change': function(combo, newValue, oldValue) {
										var index = combo.getStore().findBy(function(rec) {
											return (rec.get(combo.valueField) == newValue);
										});

										combo.fireEvent('select', combo, combo.getStore().getAt(index));

										return true;
									}.createDelegate(this),
									'select': function(combo, record, id) {
										var base_form = this.findById('EvnPLStomStreamInputParams').getForm();

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
								tabIndex: TABINDEX_EPLSSIF + 9,
								width: 70,
								xtype: 'swyesnocombo'
							}, {
								id: 'EPLSSIF_DirectTypeCombo',
								listWidth: 300,
								tabIndex: TABINDEX_EPLSSIF + 11,
								width: 200,
								xtype: 'swdirecttypecombo'
							}, {
								allowDecimals: true,
								allowNegative: false,
								fieldLabel: lang['ukl'],
								id: 'EPLSSIF_EvnPLStom_UKL',
								maxValue: 1,
								name: 'EvnPLStom_UKL',
								tabIndex: TABINDEX_EPLSSIF + 13,
								width: 70,
								value: 1,
								xtype: 'numberfield',
								enableKeyEvents: true,
								listeners:{
									'keydown':function (inp, e) {
										if (e.getKey() == Ext.EventObject.TAB) {
											if (!e.shiftKey) {
												e.stopEvent();
												var gr = that.findById('EPLSSIF_EvnPLStomGrid').getGrid();
												if (gr.getStore().getCount() > 0) {
													gr.getView().focusRow(0);
													gr.getSelectionModel().selectFirstRow();
												}
											}
										}
									}
								}
							}]
						}, {
							border: false,
							labelAlign: 'right',
							layout: 'form',
							labelWidth: 150,
							items: [{
								id: 'EPLSSIF_LpuBuildingCombo',
								lastQuery: '',
								listWidth: 600,
								linkedElements: [
									'EPLSSIF_LpuSectionCombo'
								],
								tabIndex: TABINDEX_EPLSSIF + 5,
								width: 200,
								xtype: 'swlpubuildingglobalcombo'
							}, {
								id: 'EPLSSIF_LpuSectionCombo',
								linkedElements: [
									'EPLSSIF_MedPersonalCombo'
								],
								listWidth: 600,
								parentElementId: 'EPLSSIF_LpuBuildingCombo',
								tabIndex: TABINDEX_EPLSSIF + 6,
								width: 200,
								xtype: 'swlpusectionglobalcombo'
							}, {
								hiddenName: 'MedStaffFact_id',
								id: 'EPLSSIF_MedPersonalCombo',
								parentElementId: 'EPLSSIF_LpuSectionCombo',
								listWidth: 600,
								tabIndex: TABINDEX_EPLSSIF + 7,
								width: 300,
								xtype: 'swmedstafffactglobalcombo'
							}, {
								fieldLabel: lang['sredniy_m_pers'],
								hiddenName: 'MedStaffFact_sid',
								id: 'EPLSSIF_MedPersonalMidCombo',
								listWidth: 600,
								// parentElementId: 'EPLSSIF_LpuSectionCombo',
								tabIndex: TABINDEX_EPLSSIF + 8,
								width: 300,
								xtype: 'swmedstafffactglobalcombo'
							}, {
								id: 'EPLSSIF_ResultClassCombo',
								tabIndex: TABINDEX_EPLSSIF + 10,
								width: 200,
								xtype: 'swresultclasscombo'
							}, {
								id: 'EPLSSIF_DirectClassCombo',
								tabIndex: TABINDEX_EPLSSIF + 12,
								width: 200,
								xtype: 'swdirectclasscombo'
							}, {
								border: false,
								hidden: getRegionNick() != 'kareliya',
								layout: 'form',
								xtype: 'panel',
								items: [{
									hiddenName: 'MedicalCareKind_id',
									id: 'EPLSSIF_MedicalCareKindCombo',
									fieldLabel: lang['meditsinskaya_pomosch'],
									comboSubject: 'MedicalCareKind',
									xtype: 'swcommonsprcombo',
									width: 300
								}]
							}]
						}]
					}],
					//labelWidth: 130,
					title: lang['parametryi_vvoda']
				})]
			},
				EPLSSIF_EvnPLStomGrid
			]
		});
		sw.Promed.swEvnPLStomStreamInputWindow.superclass.initComponent.apply(this, arguments);

		if((getGlobalOptions().region && getGlobalOptions().region.nick== 'ufa')){
			this.findById('EPLSSIF_LpuSectionCombo').addListener('change', function(combo, newValue, oldValue) {
			   // this.setLpuSectionProfile();
			   // this.loadMesCombo();

				if ( getRegionNick() == 'ufa' ) {
					var base_form = this.findById('EvnPLStomStreamInputParams').getForm();

					var uslugacomplex_combo = base_form.findField('UslugaComplex_uid');

					var usluga_complex_id = uslugacomplex_combo.getValue();

					if ( !newValue ) {
						uslugacomplex_combo.setLpuLevelCode(0);
						return false;
					}

					var index = combo.getStore().findBy(function(rec) {
						if ( rec.get('LpuSection_id') == newValue ) {
							return true;
						}
						else {
							return false;
						}
					});
					var record = combo.getStore().getAt(index);

					uslugacomplex_combo.clearValue();
					uslugacomplex_combo.lastQuery = 'This query sample that is not will never appear';
					uslugacomplex_combo.getStore().removeAll();
					uslugacomplex_combo.getStore().baseParams.query = '';

					if ( record ) {
						uslugacomplex_combo.setLpuLevelCode(record.get('LpuSectionProfile_Code'));
						uslugacomplex_combo.getStore().load({
							callback: function() {
								index = uslugacomplex_combo.getStore().findBy(function(rec) {
									return (rec.get('UslugaComplex_id') == usluga_complex_id);
								});

								if ( index >= 0 ) {
									uslugacomplex_combo.setValue(usluga_complex_id);
								}
							}
						});
					}
				}
			}.createDelegate(this));
			this.findById('EPLSSIF_MedPersonalCombo').addListener('change', function(combo, newValue, oldValue) {
            if ( getGlobalOptions().region && getGlobalOptions().region.nick == 'ufa' ) {
                var base_form = this.findById('EvnPLStomStreamInputParams').getForm();

                var uslugacomplex_combo = base_form.findField('UslugaComplex_uid');

                var usluga_complex_id = uslugacomplex_combo.getValue();

                if ( !newValue ) {
                    uslugacomplex_combo.setLpuLevelCode(0);
                    return false;
                }

                var index = combo.getStore().findBy(function(rec) {
                    if ( rec.get('MedStaffFact_id') == newValue ) {
                        return true;
                    }
                    else {
                        return false;
                    }
                });
                var record = combo.getStore().getAt(index);

                uslugacomplex_combo.clearValue();
                uslugacomplex_combo.lastQuery = 'This query sample that is not will never appear';
                uslugacomplex_combo.getStore().removeAll();
                uslugacomplex_combo.getStore().baseParams.query = '';

                if ( record ) {
                    uslugacomplex_combo.setLpuLevelCode(record.get('LpuSectionProfile_Code'));
                    uslugacomplex_combo.getStore().load({
                        callback: function() {
                            index = uslugacomplex_combo.getStore().findBy(function(rec) {
                                return (rec.get('UslugaComplex_id') == usluga_complex_id);
                            });

                            if ( index >= 0 ) {
                                uslugacomplex_combo.setValue(usluga_complex_id);
                            }
                        }
                    });
                }
            } 
        }.createDelegate(this));
		}
		//focusing viewframes
		EPLSSIF_EvnPLStomGrid.focusPrev = this.findById('EPLSSIF_EvnPLStom_UKL');
		EPLSSIF_EvnPLStomGrid.focusPrev.type = 'field';
		EPLSSIF_EvnPLStomGrid.focusPrev.name = EPLSSIF_EvnPLStomGrid.focusPrev.id;
		EPLSSIF_EvnPLStomGrid.focusOn = this.buttons[2];
		EPLSSIF_EvnPLStomGrid.focusOn.type = 'field';
		EPLSSIF_EvnPLStomGrid.focusOn.name = EPLSSIF_EvnPLStomGrid.focusOn.id;
	},
	keys: [{
		fn: function(inp, e) {
			Ext.getCmp('EvnPLStomStreamInputWindow').openEvnPLStomEditWindow('add');
		},
		key: [
			Ext.EventObject.INSERT
		],
		stopEvent: true
	}, {
		alt: true,
		fn: function(inp, e) {
			Ext.getCmp('EvnPLStomStreamInputWindow').hide();
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
	openEvnPLStomEditWindow: function(action) {
		if ( action != 'add' && action != 'edit' && action != 'view' ) {
			return false;
		}

		if ( action == 'add' && getWnd('swPersonSearchWindow').isVisible() ) {
			sw.swMsg.alert(lang['soobschenie'], lang['okno_poiska_cheloveka_uje_otkryito']);
			return false;
		}

		if ( getWnd('swEvnPLStomEditWindow').isVisible() ) {
			sw.swMsg.alert(lang['soobschenie'], lang['okno_redaktirovaniya_talona_ambulatornogo_patsienta_uje_otkryito']);
			return false;
		}

		var grid = this.findById('EPLSSIF_EvnPLStomGrid').getGrid();

		if ( !grid ) {
			sw.swMsg.alert(lang['oshibka'], lang['ne_udalos_nayti_spisok_talonov']);
			return false;
		}

		var index;
		var params = new Object();

		params.action = action;
		params.callback = function(data) {
			if ( !data || !data.evnPLStomData ) {
				return false;
			}

			var record = grid.getStore().getById(data.evnPLStomData.EvnPLStom_id);

			if ( !record ) {
				if ( grid.getStore().getCount() == 1 && !grid.getStore().getAt(0).get('EvnPLStom_id') ) {
					grid.getStore().removeAll();
				}

				grid.getStore().loadData({ 'data': [ data.evnPLStomData ]}, true);
			}
			else {
				var evn_pl_stom_fields = new Array();

				grid.getStore().fields.eachKey(function(key, item) {
					evn_pl_stom_fields.push(key);
				});

				for ( i = 0; i < evn_pl_stom_fields.length; i++ ) {
					record.set(evn_pl_stom_fields[i], data.evnPLStomData[evn_pl_stom_fields[i]]);
				}

				record.commit();
			}
		}.createDelegate(this);
		params.streamInput = true;

		if ( action == 'add' ) {
			params.DirectClass_id = this.findById('EPLSSIF_DirectClassCombo').getValue();
			params.DirectType_id = this.findById('EPLSSIF_DirectTypeCombo').getValue();
			params.EvnPLStom_IsFinish = this.findById('EPLSSIF_IsFinishCombo').getValue();
			params.EvnPLStom_UKL = this.findById('EPLSSIF_EvnPLStom_UKL').getValue();
			params.EvnVizitPLStom_setDate = this.findById('EPLSSIF_EvnVizitPLStom_setDate').getValue();
			params.LpuSection_id = this.findById('EPLSSIF_LpuSectionCombo').getValue();
			params.MedStaffFact_id = this.findById('EPLSSIF_MedPersonalCombo').getValue();
			params.MedStaffFact_sid = this.findById('EPLSSIF_MedPersonalMidCombo').getValue();
			params.PayType_id = this.findById('EPLSSIF_PayTypeCombo').getValue();
			params.MedPersonal_id = null;
			params.MedPersonal_sid = null;
			params.onHide = function() {
				// TODO: Здесь надо будет переделать использование getWnd
				getWnd('swPersonSearchWindow').findById('person_search_form').getForm().findField('PersonSurName_SurName').focus(true, 500);
			};
			//вид оплаты по умолчанию итак выставляется, а передача не null ломает фильтрацию цели посещения
			//params.PayType_id = this.findById('EPLSSIF_PayTypeCombo').getValue();

			params.ResultClass_id = this.findById('EPLSSIF_ResultClassCombo').getValue();
			params.ServiceType_id = this.findById('EPLSSIF_ServiceTypeCombo').getValue();
			params.VizitType_id = this.findById('EPLSSIF_VizitTypeCombo').getValue();
			params.UslugaComplex_uid = this.findById('EPLSSIF_UslugaComplex').getValue();
			params.MedicalCareKind_id = this.findById('EPLSSIF_MedicalCareKindCombo').getValue();

			index = this.findById('EPLSSIF_MedPersonalCombo').getStore().findBy(function(rec) {
				return (rec.get('MedStaffFact_id') == this.findById('EPLSSIF_MedPersonalCombo').getValue());
			}.createDelegate(this));

			if ( index >= 0 ) {
				params.MedPersonal_id = this.findById('EPLSSIF_MedPersonalCombo').getStore().getAt(index).get('MedPersonal_id');
			}

			index = this.findById('EPLSSIF_MedPersonalMidCombo').getStore().findBy(function(rec) {
				return (rec.get('MedStaffFact_id') == this.findById('EPLSSIF_MedPersonalMidCombo').getValue());
			}.createDelegate(this));

			if ( index >= 0 ) {
				params.MedPersonal_sid = this.findById('EPLSSIF_MedPersonalMidCombo').getStore().getAt(index).get('MedPersonal_id');
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

					getWnd('swEvnPLStomEditWindow').show(params);
				},
				searchMode: 'all'
			});
		}
		else {
			if ( !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('EvnPLStom_id') ) {
				sw.swMsg.alert(lang['oshibka'], lang['ne_vyibran_talon_iz_spiska']);
				return false;
			}

			var record = grid.getSelectionModel().getSelected();

			params.EvnPLStom_id = record.get('EvnPLStom_id');
			params.onHide = function() {
				grid.getView().focusRow(grid.getStore().indexOf(record));
				grid.getSelectionModel().selectRow(grid.getStore().indexOf(record));
			};
			params.Person_id = record.get('Person_id');
			params.PersonEvn_id = record.get('PersonEvn_id');
			params.Server_id = record.get('Server_id');

			getWnd('swEvnPLStomEditWindow').show(params);
		}
	},
	plain: true,
	pmUser_Name: null,
	printEvnPLStom: function() {
		var grid = this.findById('EPLSSIF_EvnPLStomGrid').ViewGridPanel;

		if ( !grid.getSelectionModel().getSelected() ) {
			return false;
		}

		var evn_pl_stom_id = grid.getSelectionModel().getSelected().get('EvnPLStom_id');

		if ( evn_pl_stom_id ) {
			printEvnPL({
				type: 'EvnPLStom',
				EvnPL_id: evn_pl_stom_id
			});
		}
	},
	refreshEvnPLStomGrid: function() {
		var grid = this.findById('EPLSSIF_EvnPLStomGrid').getGrid();

		grid.getSelectionModel().clearSelections();
		grid.getStore().reload();

		if ( grid.getStore().getCount() > 0 ) {
			grid.getView().focusRow(0);
			grid.getSelectionModel().selectFirstRow();
		}
	},
	resizable: false,
	setBegDateTime: function() {
		Ext.Ajax.request({
			callback: function(opt, success, response) {
				if ( success && response.responseText != '' ) {
					var response_obj = Ext.util.JSON.decode(response.responseText);

					this.begDate = response_obj.begDate;
					this.begTime = response_obj.begTime;

					this.findById('EPLSSIF_StreamInformationForm').findById('EPLSSIF_pmUser_Name').setValue(response_obj.pmUser_Name);
					this.findById('EPLSSIF_StreamInformationForm').findById('EPLSSIF_Stream_begDateTime').setValue(response_obj.begDate + ' ' + response_obj.begTime);
					this.findById('EPLSSIF_EvnPLStomGrid').getGrid().getStore().baseParams.begDate = response_obj.begDate;
					this.findById('EPLSSIF_EvnPLStomGrid').getGrid().getStore().baseParams.begTime = response_obj.begTime;
					this.findById('EPLSSIF_UslugaComplex').getStore().baseParams.UslugaComplex_Date = response_obj.begDate;
				}
			}.createDelegate(this),
			url: C_LOAD_CURTIME
		});
	},
	show: function() {
		sw.Promed.swEvnPLStomStreamInputWindow.superclass.show.apply(this, arguments);

		this.restore();
		this.center();
		this.maximize();

		this.begDate = null;
		this.begTime = null;
		this.pmUser_Name = null;

		var base_form = this.findById('EvnPLStomStreamInputParams').getForm();
		base_form.reset();

		swLpuBuildingGlobalStore.clearFilter();
		base_form.findField('LpuBuilding_id').getStore().loadData(getStoreRecords(swLpuBuildingGlobalStore));

		base_form.findField('EvnVizitPLStom_setDate').fireEvent('change', base_form.findField('EvnVizitPLStom_setDate'), '');

		// Заполнение умолчаний - было сделано по умолчанию value на компоненте, но в других регионах ID другие, поэтому так.
		// (http://172.19.61.24:85/issues/show/2094)
		var PayType_SysNick = 'oms';
		switch ( getRegionNick() ) {
			case 'by': PayType_SysNick = 'besus'; break;
			case 'kz': PayType_SysNick = 'Resp'; break;
		}
		base_form.findField('PayType_id').setFieldValue('PayType_SysNick', PayType_SysNick);

		if (getGlobalOptions().region && getGlobalOptions().region.nick == 'kareliya') {
			base_form.findField('MedicalCareKind_id').setFieldValue('MedicalCareKind_Code', 9);
		}
		if (getGlobalOptions().region && getGlobalOptions().region.nick== 'ufa') {
				base_form.findField('UslugaComplex_uid').setUslugaCategoryList([ 'lpusection' ]);
				base_form.findField('UslugaComplex_uid').setAllowedUslugaComplexAttributeList([ 'stom' ]);
				base_form.findField('UslugaComplex_uid').getStore().baseParams.allowMorbusVizitOnly = 0;
				base_form.findField('UslugaComplex_uid').getStore().baseParams.allowNonMorbusVizitOnly = 0;
				base_form.findField('UslugaComplex_uid').getStore().baseParams.allowedUslugaComplexAttributeMethod = 'or';
			}
		// Заполнение полей "Пользователь" и "Дата начала ввода"
		this.setBegDateTime();

		var gr = this.findById('EPLSSIF_EvnPLStomGrid');
		gr.getGrid().getStore().removeAll();
		gr.addEmptyRecord(gr.getGrid().getStore());
		setTimeout(function (){
			gr.getGrid().getView().focusRow(0);
			gr.getGrid().getSelectionModel().selectFirstRow();
		}, 500)

	},
	title: WND_POL_EPLSTIN + ' (стоматология)',
	width: 800
});