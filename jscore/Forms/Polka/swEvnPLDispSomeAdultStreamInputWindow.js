/**
* swEvnPLDispSomeAdultStreamInputWindow - окно потокового ввода талонов амбулаторного пациента.
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
* @comment      Префикс для id компонентов EPLDSASIF (EvnPLDispSomeAdultStreamInput)
*/

sw.Promed.swEvnPLDispSomeAdultStreamInputWindow = Ext.extend(sw.Promed.BaseForm, {
	begDate: null,
	begTime: null,
	buttonAlign: 'left',
	closable: true,
	closeAction: 'hide',
	formName: 'EvnPLDispSomeAdultStreamInputParams',
	collapsible: true,
	deleteEvnPL: function() {
		var grid = this.findById('EPLDSASIF_EvnPLGrid');

		if ( !grid || !grid.getGrid() ) {
			sw.swMsg.alert(lang['oshibka'], lang['pri_udalenii_talona_voznikli_oshibki_[tip_oshibki_1]']);
			return false;
		}
		else if ( !grid.getGrid().getSelectionModel().getSelected() ) {
			sw.swMsg.alert(lang['oshibka'], lang['ne_vyibran_talon_iz_spiska']);
			return false;
		}

		var selected_record = grid.getGrid().getSelectionModel().getSelected();
		var evn_pl_id = selected_record.get('EvnPL_id');

		if ( evn_pl_id == null ) {
			return false;
		}

		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function(buttonId, text, obj) {
				if ( 'yes' == buttonId ) {
					Ext.Ajax.request({
						failure: function(response, options) {
							sw.swMsg.alert(lang['oshibka'], lang['pri_udalenii_talona_voznikli_oshibki_[tip_oshibki_2]']);
						},
						params: {
							Evn_id: evn_pl_id
						},
						success: function(response, options) {
							var response_obj = Ext.util.JSON.decode(response.responseText);

							if ( response_obj.success == false ) {
								sw.swMsg.alert(lang['oshibka'], response_obj.Error_Msg ? response_obj.Error_Msg : lang['pri_udalenii_talona_voznikli_oshibki_[tip_oshibki_3]']);
							}
							else {
								grid.getGrid().getStore().remove(selected_record);

								if (grid.getGrid().getStore().getCount() == 0) {
									grid.addEmptyRecord(grid.getGrid().getStore());
								}
							}

							grid.focus();
						},
						url: '/?c=Evn&m=deleteEvn'
					});
				}
			},
			icon: Ext.MessageBox.QUESTION,
			msg: lang['udalit_talon'],
			title: lang['vopros']
		});
	},
	draggable: true,
	height: 550,
	id: 'EvnPLDispSomeAdultStreamInputWindow',
	initComponent: function() {
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
				id: 'EPLDSASIF_CancelButton',
				onTabAction: function () {
					this.findById('EPLDSASIF_EvnVizitPL_setDate').focus(true, 100);
				}.createDelegate(this),
				onShiftTabAction: function () {
					this.focusOnGrid();
				}.createDelegate(this),
				tabIndex: TABINDEX_EPLDSASIF + 15,
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
					id: 'EPLDSASIF_StreamInformationForm',
					items: [{
						disabled: true,
						fieldLabel: lang['polzovatel'],
						id: 'EPLDSASIF_pmUser_Name',
						width: 380,
						xtype: 'textfield'
					}, {
						disabled: true,
						fieldLabel: lang['data_nachala_vvoda'],
						id: 'EPLDSASIF_Stream_begDateTime',
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
					id: 'EvnPLDispSomeAdultStreamInputParams',
					items: [{
						border: false,
						layout: 'column',
						items: [{
							border: false,
							layout: 'form',
							items: [{
								fieldLabel: lang['data_posescheniya'],
								format: 'd.m.Y',
								id: 'EPLDSASIF_EvnVizitPL_setDate',
								listeners: {
									'change': function(field, newValue, oldValue) {
										var base_form = this.findById('EvnPLDispSomeAdultStreamInputParams').getForm();

										var lpu_section_id = base_form.findField('LpuSection_id').getValue();
										var med_staff_fact_id = base_form.findField('MedStaffFact_id').getValue();
										var med_staff_fact_sid = base_form.findField('MedStaffFact_sid').getValue();
										var ServiceType_id = base_form.findField('ServiceType_id').getValue();

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

										if ( !Ext.isEmpty(newValue) ) {
											setLpuSectionGlobalStoreFilter({
												allowLowLevel: 'yes',
												isPolka: true
											});
											setMedStaffFactGlobalStoreFilter({
												allowLowLevel: 'yes',
												isPolka: true
											});
										}
										else {
											setLpuSectionGlobalStoreFilter({
												allowLowLevel: 'yes',
												isPolka: true,
												onDate: Ext.util.Format.date(newValue, 'd.m.Y')
											});

											setMedStaffFactGlobalStoreFilter({
												allowLowLevel: 'yes',
												isPolka: true,
												onDate: Ext.util.Format.date(newValue, 'd.m.Y')
											});
										}

										base_form.findField('LpuSection_id').getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));
										base_form.findField('MedStaffFact_id').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));
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
								name: 'EvnVizitPL_setDate',
								tabIndex: TABINDEX_EPLDSASIF + 1,
								width: 100,
								xtype: 'swdatefield'
							}, {
								fieldLabel: lang['mesto_posescheniya'],
								id: 'EPLDSASIF_ServiceTypeCombo',
								listWidth: 300,
								tabIndex: TABINDEX_EPLDSASIF + 2,
								width: 200,
								xtype: 'swservicetypecombo'
							}, {
								id: 'EPLDSASIF_VizitTypeCombo',
								listeners: {
									'change': function(combo, newValue, oldValue) {
										this.findById('EPLDSASIF_DiagCombo').clearValue();
										this.findById('EPLDSASIF_DiagCombo').disable();

										if ( newValue ) {
											var record = combo.getStore().getById(newValue);

											if ( record && record.get('VizitType_SysNick') == 'prof' ) {
												this.findById('EPLDSASIF_DiagCombo').enable();
											}
										}
									}.createDelegate(this)
								},
								listWidth: 300,
								tabIndex: TABINDEX_EPLDSASIF + 3,
								width: 200,
								EvnClass_id: 11,
								xtype: 'swvizittypecombo'
							}, {
								id: 'EPLDSASIF_PayTypeCombo',
								listWidth: 300,
								tabIndex: TABINDEX_EPLDSASIF + 4,
								width: 200,
								useCommonFilter: true,
								xtype: 'swpaytypecombo'
							}, {
								fieldLabel: lang['sluchay_zakonchen'],
								hiddenName: 'EvnPL_IsFinish',
								id: 'EPLDSASIF_IsFinishCombo',
								tabIndex: TABINDEX_EPLDSASIF + 9,
								width: 70,
								xtype: 'swyesnocombo'
							}, {
								id: 'EPLDSASIF_DirectTypeCombo',
								listWidth: 300,
								tabIndex: TABINDEX_EPLDSASIF + 11,
								width: 200,
								xtype: 'swdirecttypecombo'
							}, {
								allowDecimals: true,
								allowNegative: false,
								fieldLabel: lang['ukl'],
								id: 'EPLDSASIF_EvnPL_UKL',
								maxValue: 1,
								name: 'EvnPL_UKL',
								tabIndex: TABINDEX_EPLDSASIF + 13,
								width: 70,
								value: 1,
								xtype: 'numberfield',
								enableKeyEvents: true,
								listeners:{
									'keydown':function (inp, e) {
										if (e.getKey() == Ext.EventObject.TAB) {
											if (!e.shiftKey) {
												e.stopEvent();
												that.focusOnGrid();
											}
										}
									}
								}

							}]
						}, {
							border: false,
							layout: 'form',
							items: [{
								id: 'EPLDSASIF_LpuBuildingCombo',
								lastQuery: '',
								listWidth: 600,
								linkedElements: [
									'EPLDSASIF_LpuSectionCombo'
								],
								tabIndex: TABINDEX_EPLDSASIF + 5,
								width: 200,
								xtype: 'swlpubuildingglobalcombo'
							}, {
								id: 'EPLDSASIF_LpuSectionCombo',
								linkedElements: [
									'EPLDSASIF_MedPersonalCombo'
								],
								listWidth: 600,
								parentElementId: 'EPLDSASIF_LpuBuildingCombo',
								tabIndex: TABINDEX_EPLDSASIF + 6,
								width: 200,
								xtype: 'swlpusectionglobalcombo'
							}, {
								hiddenName: 'MedStaffFact_id',
								id: 'EPLDSASIF_MedPersonalCombo',
								parentElementId: 'EPLDSASIF_LpuSectionCombo',
								listWidth: 600,
								tabIndex: TABINDEX_EPLDSASIF + 7,
								width: 300,
								xtype: 'swmedstafffactglobalcombo'
							}, {
								fieldLabel: lang['sredniy_m_pers'],
								hiddenName: 'MedStaffFact_sid',
								id: 'EPLDSASIF_MedPersonalMidCombo',
								listWidth: 600,
								//parentElementId: 'EPLDSASIF_LpuSectionCombo',
								tabIndex: TABINDEX_EPLDSASIF + 8,
								width: 300,
								xtype: 'swmedstafffactglobalcombo'
							}, {
								id: 'EPLDSASIF_ResultClassCombo',
								tabIndex: TABINDEX_EPLDSASIF + 10,
								width: 200,
								xtype: 'swresultclasscombo'
							}, {
								id: 'EPLDSASIF_DirectClassCombo',
								tabIndex: TABINDEX_EPLDSASIF + 12,
								width: 200,
								xtype: 'swdirectclasscombo'
							}, {
								hiddenName: 'Diag_id',
								id: 'EPLDSASIF_DiagCombo',
								listWidth: 600,
								tabIndex: TABINDEX_EPLDSASIF + 14,
								width: 300,
								xtype: 'swdiagcombo'
							}]
						}]
					}],
					labelAlign: 'right',
					labelWidth: 130,
					title: lang['parametryi_vvoda']
				})]
			},
			new sw.Promed.ViewFrame({
				actions: [
					{ name: 'action_add', handler: function() { this.openEvnPLDispSomeAdultEditWindow('add'); }.createDelegate(this) },
					{ name: 'action_edit', handler: function() { this.openEvnPLDispSomeAdultEditWindow('edit'); }.createDelegate(this) },
					{ name: 'action_view', handler: function() { this.openEvnPLDispSomeAdultEditWindow('view'); }.createDelegate(this) },
					{ name: 'action_delete', handler: function() { this.deleteEvnPL(); }.createDelegate(this) },
					{ name: 'action_refresh', handler: function() { this.refreshEvnPLGrid(); }.createDelegate(this) },
					{ name: 'action_print', handler: function() { this.printEvnPL(); }.createDelegate(this) }
				],
				autoExpandColumn: 'autoexpand',
				autoExpandMin: 150,
				autoLoadData: false,
				dataUrl: '/?c=EvnPL&m=loadEvnPLStreamList',
				focusOn: {
					name: 'EPLDSASIF_CancelButton',
					type: 'button'
				},
				focusPrev: {
					name: 'EPLDSASIF_EvnPL_UKL',
					type: 'field'
				},
				id: 'EPLDSASIF_EvnPLGrid',
				pageSize: 100,
				paging: false,
				region: 'center',
				root: 'data',
				stringfields: [
					{ name: 'EvnPL_id', type: 'int', header: 'ID', key: true },
					{ name: 'Person_id', type: 'int', hidden: true },
					{ name: 'PersonEvn_id', type: 'int', hidden: true },
					{ name: 'Server_id', type: 'int', hidden: true },
					{ name: 'EvnPL_NumCard', type: 'string', header: lang['№_talona'], width: 70 },
					{ name: 'Person_Surname', type: 'string', header: lang['familiya'], id: 'autoexpand' },
					{ name: 'Person_Firname', type: 'string', header: lang['imya'], width: 150 },
					{ name: 'Person_Secname', type: 'string', header: lang['otchestvo'], width: 150 },
					{ name: 'Person_Birthday', type: 'date', format: 'd.m.Y', header: lang['d_r'], width: 100 },
					{ name: 'EvnPL_setDate', type: 'date', format: 'd.m.Y', header: lang['data_nachala'], width: 100 },
					{ name: 'EvnPL_disDate', type: 'date', format: 'd.m.Y', header: lang['data_okonchaniya'], width: 100 },
					{ name: 'EvnPL_VizitCount', type: 'int', header: lang['posescheniy'], width: 100 },
					{ name: 'EvnPL_IsFinish', type: 'string', header: lang['zakonch'], width: 100 }
				],
				toolbar: true,
				totalProperty: 'totalCount'
			})]
		});
		sw.Promed.swEvnPLDispSomeAdultStreamInputWindow.superclass.initComponent.apply(this, arguments);

		//focusing viewframes
		var grid = this.findById('EPLDSASIF_EvnPLGrid');
		grid.focusPrev = this.findById('EPLDSASIF_EvnPL_UKL');
		grid.focusPrev.type = 'field';
		grid.focusPrev.name = grid.focusPrev.id;
		grid.focusOn = this.buttons[2];
		grid.focusOn.type = 'field';
		grid.focusOn.name = grid.focusOn.id;

		this.focusOnGrid = function () {
			var grid = that.findById('EPLDSASIF_EvnPLGrid').getGrid();
			if (grid.getStore().getCount() > 0) {
				grid.getView().focusRow(0);
				grid.getSelectionModel().selectFirstRow();
			}
		}

	},
	keys: [{
		fn: function(inp, e) {
			Ext.getCmp('EvnPLDispSomeAdultStreamInputWindow').openEvnPLDispSomeAdultEditWindow('add');
		},
		key: [
			Ext.EventObject.INSERT
		],
		stopEvent: true
	}, {
		alt: true,
		fn: function(inp, e) {
			Ext.getCmp('EvnPLDispSomeAdultStreamInputWindow').hide();
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
	openEvnPLDispSomeAdultEditWindow: function(action) {
		if ( action != 'add' && action != 'edit' && action != 'view' ) {
			return false;
		}

		if ( action == 'add' && getWnd('swPersonSearchWindow').isVisible() ) {
			sw.swMsg.alert(lang['soobschenie'], lang['okno_poiska_cheloveka_uje_otkryito']);
			return false;
		}

		if ( getWnd('swEvnPLDispSomeAdultEditWindow').isVisible() ) {
			sw.swMsg.alert(lang['soobschenie'], lang['okno_redaktirovaniya_talona_ambulatornogo_patsienta_uje_otkryito']);
			return false;
		}

		var grid = this.findById('EPLDSASIF_EvnPLGrid').getGrid();

		if ( !grid ) {
			sw.swMsg.alert(lang['oshibka'], lang['ne_udalos_nayti_spisok_talonov']);
			return false;
		}

		var index;
		var params = new Object();

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

				grid.getStore().loadData({ 'data': [ data.evnPLData ]}, true);
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
			params.Diag_id = this.findById('EPLDSASIF_DiagCombo').getValue();
			params.DirectClass_id = this.findById('EPLDSASIF_DirectClassCombo').getValue();
			params.DirectType_id = this.findById('EPLDSASIF_DirectTypeCombo').getValue();
			params.EvnPL_IsFinish = this.findById('EPLDSASIF_IsFinishCombo').getValue();
			params.EvnPL_UKL = this.findById('EPLDSASIF_EvnPL_UKL').getValue();
			params.EvnVizitPL_setDate = this.findById('EPLDSASIF_EvnVizitPL_setDate').getValue();
			params.LpuSection_id = this.findById('EPLDSASIF_LpuSectionCombo').getValue();
			params.MedPersonal_id = null;
			params.MedPersonal_sid = null;
			params.onHide = function() {
				// TODO: Здесь надо будет переделать использование getWnd
				getWnd('swPersonSearchWindow').findById('person_search_form').getForm().findField('PersonSurName_SurName').focus(true, 500);
			};
			params.PayType_id = this.findById('EPLDSASIF_PayTypeCombo').getValue();
			params.ResultClass_id = this.findById('EPLDSASIF_ResultClassCombo').getValue();
			params.ServiceType_id = this.findById('EPLDSASIF_ServiceTypeCombo').getValue();
			params.VizitType_id = this.findById('EPLDSASIF_VizitTypeCombo').getValue();

			index = this.findById('EPLDSASIF_MedPersonalCombo').getStore().findBy(function(rec) {
				return (rec.get('MedStaffFact_id') == this.findById('EPLDSASIF_MedPersonalCombo').getValue());
			}.createDelegate(this));

			if ( index >= 0 ) {
				params.MedPersonal_id = this.findById('EPLDSASIF_MedPersonalCombo').getStore().getAt(index).get('MedPersonal_id');
			}

			index = this.findById('EPLDSASIF_MedPersonalMidCombo').getStore().findBy(function(rec) {
				return (rec.get('MedStaffFact_id') == this.findById('EPLDSASIF_MedPersonalMidCombo').getValue());
			}.createDelegate(this));

			if ( index >= 0 ) {
				params.MedPersonal_sid = this.findById('EPLDSASIF_MedPersonalMidCombo').getStore().getAt(index).get('MedPersonal_id');
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

					getWnd('swEvnPLDispSomeAdultEditWindow').show(params);
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

			params.EvnPL_id = record.get('EvnPL_id');
			params.onHide = function() {
				grid.getView().focusRow(grid.getStore().indexOf(record));
				grid.getSelectionModel().selectRow(grid.getStore().indexOf(record));
			};
			params.Person_id = record.get('Person_id');
			params.PersonEvn_id = record.get('PersonEvn_id');
			params.Server_id = record.get('Server_id');

			getWnd('swEvnPLDispSomeAdultEditWindow').show(params);
		}
	},
	plain: true,
	pmUser_Name: null,
	printEvnPL: function() {
		var grid = this.findById('EPLDSASIF_EvnPLGrid').ViewGridPanel;

		if ( !grid.getSelectionModel().getSelected() ) {
			return false;
		}

		var evn_pl_id = grid.getSelectionModel().getSelected().get('EvnPL_id');

		if ( evn_pl_id ) {
			if(getGlobalOptions().region.nick == 'penza'){ //https://redmine.swan.perm.ru/issues/63097
				printBirt({
					'Report_FileName': 'EvnPLPrint.rptdesign',
					'Report_Params': '&paramEvnPL=' + evn_pl_id,
					'Report_Format': 'pdf'
				});
			}
			else
				window.open('/?c=EvnPL&m=printEvnPL&EvnPL_id=' + evn_pl_id, '_blank');
		}
	},
	refreshEvnPLGrid: function() {
		var grid = this.findById('EPLDSASIF_EvnPLGrid').getGrid();

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

					this.findById('EPLDSASIF_StreamInformationForm').findById('EPLDSASIF_pmUser_Name').setValue(response_obj.pmUser_Name);
					this.findById('EPLDSASIF_StreamInformationForm').findById('EPLDSASIF_Stream_begDateTime').setValue(response_obj.begDate + ' ' + response_obj.begTime);
					this.findById('EPLDSASIF_EvnPLGrid').getGrid().getStore().baseParams.begDate = response_obj.begDate;
					this.findById('EPLDSASIF_EvnPLGrid').getGrid().getStore().baseParams.begTime = response_obj.begTime;
				}
			}.createDelegate(this),
			url: C_LOAD_CURTIME
		});
	},
	show: function() {
		sw.Promed.swEvnPLDispSomeAdultStreamInputWindow.superclass.show.apply(this, arguments);

		this.restore();
		this.center();
		this.maximize();

		this.begDate = null;
		this.begTime = null;
		this.pmUser_Name = null;

		var base_form = this.findById('EvnPLDispSomeAdultStreamInputParams').getForm();
		base_form.reset();

		base_form.findField('Diag_id').disable();

		swLpuBuildingGlobalStore.clearFilter();
		base_form.findField('LpuBuilding_id').getStore().loadData(getStoreRecords(swLpuBuildingGlobalStore));

		base_form.findField('EvnVizitPL_setDate').fireEvent('change', base_form.findField('EvnVizitPL_setDate'), '');

		// Заполнение полей "Пользователь" и "Дата начала ввода"
		this.setBegDateTime();

		var grid = this.findById('EPLDSASIF_EvnPLGrid');
		grid.removeAll();
		setTimeout(this.focusOnGrid, 500);
	},
	title: WND_POL_EPLDSASTIN,
	width: 800
});