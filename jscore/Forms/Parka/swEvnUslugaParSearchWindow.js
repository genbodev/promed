/**
* swEvnUslugaParSearchWindow - окно поиска параклинических услуг.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Parka
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Stas Bykov aka Savage (savage1981@gmail.com)
* @version      0.001-13.05.2010
* @comment      Префикс для id компонентов EUPSW (EvnUslugaParSearchWindow)
*
*
* Использует: окно редактирования параклинической услуги (swEvnUslugaParEditWindow)
*             окно поиска организации (swOrgSearchWindow)
*             окно поиска человека (swPersonSearchWindow)
*/

sw.Promed.swEvnUslugaParSearchWindow = Ext.extend(sw.Promed.BaseForm, {
	buttonAlign: 'left',
	closable: true,
	closeAction: 'hide',
	collapsible: true,
	deleteEvnUslugaPar: function() {
		var grid = this.findById('EUPSW_EvnUslugaParSearchGrid').getGrid();

		if ( !grid || !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('EvnUslugaPar_id') ) {
			return false;
		}

		var record = grid.getSelectionModel().getSelected();
		var evn_usluga_par_id = record.get('EvnUslugaPar_id');

		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function(buttonId, text, obj) {
				if ( buttonId == 'yes' ) {
					Ext.Ajax.request({
						callback: function(options, success, response) {
							if ( success ) {
								var response_obj = Ext.util.JSON.decode(response.responseText);

								if ( response_obj.success == false ) {
									sw.swMsg.alert(lang['oshibka'], response_obj.Error_Msg ? response_obj.Error_Msg : lang['oshibka_pri_udalenii_paraklinicheskoy_uslugi']);
								}
								else {
									grid.getStore().remove(record);

									if ( grid.getStore().getCount() == 0 ) {
										LoadEmptyRow(grid, 'data');
									}
								}

								grid.getView().focusRow(0);
								grid.getSelectionModel().selectFirstRow();
							}
							else {
								sw.swMsg.alert(lang['oshibka'], lang['pri_udalenii_paraklinicheskoy_uslugi_voznikli_oshibki']);
							}
						},
						params: {
							EvnUslugaPar_id: evn_usluga_par_id
						},
						url: '/?c=EvnUslugaPar&m=deleteEvnUslugaPar'
					});
				}
			},
			icon: Ext.MessageBox.QUESTION,
			msg: lang['udalit_paraklinicheskuyu_uslugu'],
			title: lang['vopros']
		});
	},
	doReset: function() {
		var base_form = this.findById('EvnUslugaParSearchFilterForm').getForm();

		base_form.reset();

		if ( base_form.findField('AttachLpu_id') != null ) {
			base_form.findField('AttachLpu_id').fireEvent('change', base_form.findField('AttachLpu_id'), 0, 1);
		}

		if ( base_form.findField('LpuRegion_id') != null ) {
			base_form.findField('LpuRegion_id').lastQuery = '';
			base_form.findField('LpuRegion_id').getStore().clearFilter();
		}

		if ( base_form.findField('PrivilegeType_id') != null ) {
			base_form.findField('PrivilegeType_id').lastQuery = '';
			base_form.findField('PrivilegeType_id').getStore().filterBy(function(record) {
				if ( record.get('PrivilegeType_Code') <= 500 ) {
					return true;
				}
				else {
					return false;
				}
			});
		}

		if ( base_form.findField('LpuRegionType_id') != null ) {
			base_form.findField('LpuRegionType_id').getStore().clearFilter();
		}

		if ( base_form.findField('PersonCardStateType_id') != null ) {
			base_form.findField('PersonCardStateType_id').fireEvent('change', base_form.findField('PersonCardStateType_id'), 1, 0);
		}

		if ( base_form.findField('PrehospDirect_id') != null ) {
			base_form.findField('PrehospDirect_id').fireEvent('change', base_form.findField('PrehospDirect_id'), null);
		}

		if ( base_form.findField('PrivilegeStateType_id') != null ) {
			base_form.findField('PrivilegeStateType_id').fireEvent('change', base_form.findField('PrivilegeStateType_id'), 1, 0);
		}

		if ( base_form.findField('UslugaPlace_id') != null ) {
			base_form.findField('UslugaPlace_id').fireEvent('change', base_form.findField('UslugaPlace_id'), null);
		}

		setLpuSectionGlobalStoreFilter({LpuSection_id: ""});
		base_form.findField('LpuSection_did').getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));

		this.findById('EUPSW_SearchFilterTabbar').setActiveTab(0);
		this.findById('EUPSW_SearchFilterTabbar').getActiveTab().fireEvent('activate', this.findById('EUPSW_SearchFilterTabbar').getActiveTab());

		this.findById('EUPSW_EvnUslugaParSearchGrid').getGrid().getStore().removeAll();
	},
	searchInProgress: false,
	doSearch: function(params) {
		if (this.searchInProgress) {
			log(lang['poisk_uje_vyipolnyaetsya']);
			return false;
		} else {
			this.searchInProgress = true;
		}
		var thisWindow = this;

		if ( params && params['soc_card_id'] )
			var soc_card_id = params['soc_card_id'];
	
		var base_form = this.findById('EvnUslugaParSearchFilterForm').getForm();
		
		if ( this.findById('EvnUslugaParSearchFilterForm').isEmpty() && Ext.isEmpty(base_form.findField('LpuSection_uid').getValue()) ) {
			sw.swMsg.alert(lang['oshibka'], lang['ne_zapolneno_ni_odno_pole'], function() {
			});
			thisWindow.searchInProgress = false;
			return false;
		}
		
		var grid = this.findById('EUPSW_EvnUslugaParSearchGrid').getGrid();

		if ( base_form.findField('PersonPeriodicType_id').getValue() == 2 && (typeof params != 'object' || !params.ignorePersonPeriodicType ) ) {
			sw.swMsg.show({
				buttons: Ext.Msg.YESNO,
				fn: function(buttonId, text, obj) {
					if ( buttonId == 'yes' ) {
						thisWindow.searchInProgress = false;
						this.doSearch({
							ignorePersonPeriodicType: true
						});
					}
				}.createDelegate(this),
				icon: Ext.MessageBox.QUESTION,
				msg: lang['vyibran_tip_poiska_cheloveka_po_sostoyaniyu_na_moment_sluchaya_pri_vyibrannom_variante_poisk_rabotaet_znachitelno_medlennee_hotite_prodoljit_poisk'],
				title: lang['preduprejdenie']
			});
			thisWindow.searchInProgress = false;
			return false;
		}

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Подождите, идет поиск..." });
		loadMask.show();

		var post = getAllFormFieldValues(this.findById('EvnUslugaParSearchFilterForm'));
		var record;

		if ( post.PersonCardStateType_id == null ) {
			post.PersonCardStateType_id = 1;
		}

		if ( post.PrivilegeStateType_id == null ) {
			post.PrivilegeStateType_id = 1;
		}

		if (base_form.findField('LpuSection_uid').disabled) {
			post.LpuSection_uid = base_form.findField('LpuSection_uid').getValue();
		}

		if (base_form.findField('Part_of_the_study').rendered) {
			post.Part_of_the_study = base_form.findField('Part_of_the_study').getValue();
		}

		if ( base_form.findField('MedStaffFact_did').rendered ) {
			record = base_form.findField('MedStaffFact_did').getStore().getById(base_form.findField('MedStaffFact_did').getValue());

			if ( record ) {
				post.MedPersonal_did = record.get('MedPersonal_id');
			}
		}

		if ( base_form.findField('MedStaffFact_uid').rendered ) {
			record = base_form.findField('MedStaffFact_uid').getStore().getById(base_form.findField('MedStaffFact_uid').getValue());

			if ( record ) {
				post.MedPersonal_uid = record.get('MedPersonal_id');
			}
		}
		
		if ( soc_card_id )
		{
			var post = {
				soc_card_id: soc_card_id,
				SearchFormType: post.SearchFormType
			};
		}

		post.limit = 100;
		post.start = 0;
		if (!Ext.isEmpty(post.autoLoadArchiveRecords)) {
			this.findById('EUPSW_EvnUslugaParSearchGrid').showArchive = true;
		} else {
			this.findById('EUPSW_EvnUslugaParSearchGrid').showArchive = false;
		}

		grid.getStore().baseParams = post;
		if ( base_form.isValid() ) {
			this.findById('EUPSW_EvnUslugaParSearchGrid').ViewActions.action_refresh.setDisabled(false);
			grid.getStore().removeAll();
			//grid.getStore().baseParams = '';
			grid.getStore().load({
				callback: function(records, options, success) {
					thisWindow.searchInProgress = false;
					loadMask.hide();
				},
				params: post
			});
		}
	},
	draggable: true,
	getRecordsCount: function() {
		var base_form = this.findById('EvnUslugaParSearchFilterForm').getForm();

		if ( !base_form.isValid() ) {
			sw.swMsg.alert(lang['poisk'], lang['proverte_pravilnost_zapolneniya_poley_na_forme_poiska']);
			return false;
		}

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Подождите, идет подсчет записей..." });
		loadMask.show();

		var post = getAllFormFieldValues(this.findById('EvnUslugaParSearchFilterForm'));
		var record;

		if ( post.PersonCardStateType_id == null ) {
			post.PersonCardStateType_id = 1;
		}

		if ( post.PrivilegeStateType_id == null ) {
			post.PrivilegeStateType_id = 1;
		}

		if ( base_form.findField('MedStaffFact_did').rendered ) {
			record = base_form.findField('MedStaffFact_did').getStore().getById(base_form.findField('MedStaffFact_did').getValue());

			if ( record ) {
				post.MedPersonal_did = record.get('MedPersonal_id');
			}
		}

		if ( base_form.findField('MedStaffFact_uid').rendered ) {
			record = base_form.findField('MedStaffFact_uid').getStore().getById(base_form.findField('MedStaffFact_uid').getValue());

			if ( record ) {
				post.MedPersonal_uid = record.get('MedPersonal_id');
			}
		}

		Ext.Ajax.request({
			callback: function(options, success, response) {
				loadMask.hide();

				if ( success ) {
					var response_obj = Ext.util.JSON.decode(response.responseText);

					if ( response_obj.Records_Count != undefined ) {
						sw.swMsg.alert(lang['podschet_zapisey'], lang['naydeno_zapisey'] + response_obj.Records_Count);
					}
					else {
						sw.swMsg.alert(lang['podschet_zapisey'], response_obj.Error_Msg);
					}
				}
				else {
					sw.swMsg.alert(lang['oshibka'], lang['pri_podschete_kolichestva_zapisey_proizoshli_oshibki']);
				}
			},
			params: post,
			url: C_SEARCH_RECCNT
		});
	},
	height: 550,
	id: 'EvnUslugaParSearchWindow',
	getButtonSearch: function() {
		// TODO: правильно юзать scope кнопки
		return Ext.getCmp('EUPSW_SearchButton');
	},
	printCost: function() {
		var grid = this.findById('EUPSW_EvnUslugaParSearchGrid').getGrid();
		var selected_record = grid.getSelectionModel().getSelected();
		if (selected_record && selected_record.get('EvnUslugaPar_id')) {
			getWnd('swCostPrintWindow').show({
				Evn_id: selected_record.get('EvnUslugaPar_id'),
				type: 'EvnUslugaPar',
				callback: function() {
					grid.getStore().reload();
				}
			});
		}
	},
	initComponent: function() {
		var win = this;
		var hiddenToAis25 = true;
		if(getRegionNick() == 'kz' && getGlobalOptions().AisPolkaEvnPLsync
			&& getGlobalOptions().AisPolkaEvnPLsync.lpu255and259list
			&& getGlobalOptions().lpu_id.inlist(getGlobalOptions().AisPolkaEvnPLsync.lpu255and259list))
			hiddenToAis25 = false;
		var hiddenToAis259 = true;
		if(getRegionNick() == 'kz' && getGlobalOptions().AisPolkaEvnPLsync
			&& getGlobalOptions().AisPolkaEvnPLsync.lpu259list
			&& getGlobalOptions().lpu_id.inlist(getGlobalOptions().AisPolkaEvnPLsync.lpu259list))
			hiddenToAis259 = false;
		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.doSearch();
				}.createDelegate(this),
				iconCls: 'search16',
				id: 'EUPSW_SearchButton',
				tabIndex: TABINDEX_EUPSW + 120,
				text: BTN_FRMSEARCH
			}, {
				handler: function() {
					this.doReset();
				}.createDelegate(this),
				iconCls: 'resetsearch16',
				tabIndex: TABINDEX_EUPSW + 121,
				text: BTN_FRMRESET
			}, {
				handler: function() {
					this.getRecordsCount();
				}.createDelegate(this),
				// iconCls: 'resetsearch16',
				tabIndex: TABINDEX_EUPSW + 122,
				text: BTN_FRMCOUNT
			}, {
				text: '-'
			},
			HelpButton(this, -1),
			{
				handler: function() {
					this.hide();
				}.createDelegate(this),
				iconCls: 'cancel16',
				onShiftTabAction: function() {
					this.buttons[2].focus();
				}.createDelegate(this),
				onTabAction: function() {
					this.findById('EUPSW_SearchFilterTabbar').getActiveTab().fireEvent('activate', this.findById('EUPSW_SearchFilterTabbar').getActiveTab());
				}.createDelegate(this),
				tabIndex: TABINDEX_EUPSW + 123,
				text: BTN_FRMCANCEL
			}],
			getFilterForm: function() {
				if ( this.filterForm == undefined ) {
					this.filterForm = this.findById('EvnUslugaParSearchFilterForm');
				}
				return this.filterForm;
			},
			items: [ getBaseSearchFiltersFrame({
				useArchive: 1,
				allowPersonPeriodicSelect: true,
				id: 'EvnUslugaParSearchFilterForm',
				labelWidth: 130,
				ownerWindow: this,
				searchFormType: 'EvnUslugaPar',
				tabIndexBase: TABINDEX_EUPSW,
				tabPanelHeight: 225,
				tabPanelId: 'EUPSW_SearchFilterTabbar',
				tabs: [{
					autoHeight: true,
					bodyStyle: 'margin-top: 5px;',
					border: false,
					layout: 'form',
					listeners: {
						'activate': function(panel) {
							this.getFilterForm().getForm().findField('PrehospDirect_id').focus(250, true);
						}.createDelegate(this)
					},
					title: lang['6_usluga'],

					// tabIndexStart: TABINDEX_EUPSW + 68
					items: [{
						autoHeight: true,
						labelWidth: 130,
						style: 'padding: 0px;',
						title: lang['napravlenie'],
						width: 755,
						xtype: 'fieldset',

						items: [{
							border: false,
							layout: 'column',
							items: [{
								border: false,
								layout: 'form',
								items: [{
									fieldLabel: lang['kem_napravlen'],
									hiddenName: 'PrehospDirect_id',
									automaticallyStamped: true,
									listeners: {
										'change': function(combo, newValue, oldValue) {
											var base_form = this.getFilterForm().getForm();
											var record = combo.getStore().getById(newValue);
											var comboOrg = base_form.findField('Org_did');
											var PrehospDirect_SysNick = (typeof record == 'object' ? record.get('PrehospDirect_SysNick') : '');

											switch ( true ) {
												case PrehospDirect_SysNick.inlist([ 'lpusection', 'OtdelMO' ]):
													base_form.findField('LpuSection_did').enable();
													base_form.findField('MedStaffFact_did').enable();
													break;

												default:
													if ( combo.rendered ) {
														base_form.findField('LpuSection_did').clearValue();
														base_form.findField('MedStaffFact_did').clearValue();
														comboOrg.clearValue();
														base_form.findField('LpuSection_did').disable();
														base_form.findField('MedStaffFact_did').disable();
													}
													break;
											}

											switch ( true ) {
												case PrehospDirect_SysNick.toLowerCase().inlist([ 'lpu', 'org', 'rvk', 'skor', 'admin', 'pmsp', 'kdp', 'stac', 'rdom', 'proch', 'drmo' ]):
													comboOrg.enable();
													break;

												case !Ext.isEmpty(PrehospDirect_SysNick):
													comboOrg.disable();

													if (PrehospDirect_SysNick != 'ppnd') {
														comboOrg.getStore().loadData([{
															Org_id: getGlobalOptions().org_id,
															Lpu_id: getGlobalOptions().lpu_id,
															Org_Name: getGlobalOptions().org_nick
														}]);
														comboOrg.setValue(getGlobalOptions().org_id);
													}
													break;

												default:
													if ( combo.rendered ) {
														comboOrg.disable();
														comboOrg.clearValue();
													}
													break;
											}

											combo.automaticallyStamped = false; // поставим флаг, что значение выбрал пользователь, а не проставилось автоматически
										}.createDelegate(this),
										'keydown': function (inp, e) {
											if ( e.shiftKey == true && e.getKey() == Ext.EventObject.TAB ) {
												e.stopEvent();
												// Переход к последней кнопке в окне
												this.buttons[this.buttons.length - 1].focus();
											}
										}.createDelegate(this)
									},
									tabIndex: TABINDEX_EUPSW + 68,
									width: 300,
									xtype: 'swprehospdirectcombo'
								}]
							}, {
								border: false,
								layout: 'form',
								items: [{
									fieldLabel: lang['data_napravleniya'],
									format: 'd.m.Y',
									plugins: [ new Ext.ux.InputTextMask('99.99.9999', true) ],
									name: 'EvnDirection_setDate',
									tabIndex: TABINDEX_EUPSW + 69,
									width: 100,
									xtype: 'swdatefield'
								}]
							}]
						}, {
							border: false,
							layout: 'column',
							items: [{
								border: false,
								layout: 'form',
								items: [{
									fieldLabel: lang['otdelenie'],
									hiddenName: 'LpuSection_did',
									// id: 'SS_LpuSectionCombo',
									// parentElementId: 'SS_MedStaffFactCombo',
									listeners: {
										'change': function(combo, newValue, oldValue) {
											var base_form = this.findById('EvnUslugaParSearchFilterForm').getForm();
											// base_form.findField('MedStaffFact_did').clearValue();

											setMedStaffFactGlobalStoreFilter({LpuSection_id: combo.getValue()});
											base_form.findField('MedStaffFact_did').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));

											var kemNapravlen = base_form.findField('PrehospDirect_id');
											if(newValue){
												if( !kemNapravlen.getValue() ){
													var PrehospDirect_SysNick = 'lpusection';

													if ( getRegionNick() == 'kz' ) {
														PrehospDirect_SysNick = 'OtdelMO';
													}

													kemNapravlen.setFieldValue('PrehospDirect_SysNick', PrehospDirect_SysNick);
													kemNapravlen.automaticallyStamped = true; // поставим флаг, что установили автоматически значение
												}
											}
										}.createDelegate(this)
									},
									listWidth: 600,
									tabIndex: TABINDEX_EUPSW + 70,
									width: 300,
									xtype: 'swlpusectionglobalcombo'
								}]
							}, {
								border: false,
								layout: 'form',
								items: [{
									fieldLabel: lang['№_napravleniya'],
									name: 'EvnDirection_Num',
									tabIndex: TABINDEX_EUPSW + 71,
									width: 100,
									xtype: 'numberfield'
								}]
							}]
						}, {
							border: false,
							layout: 'column',
							items: [{
								border: false,
								layout: 'form',
								items: [{
									fieldLabel: lang['vrach'],
									hiddenName: 'MedStaffFact_did',
									listWidth: 600,
									tabIndex: TABINDEX_EUPSW + 72,
									width: 300,
									xtype: 'swmedstafffactglobalcombo',
									// id: 'SS_MedStaffFactCombo',
									// parentElementId: 'SS_LpuSectionCombo',
									listeners: {
										'change': function(combo, newValue, oldValue){
											var base_form = this.findById('EvnUslugaParSearchFilterForm').getForm();
											var kemNapravlen = base_form.findField('PrehospDirect_id');
											var otdelenie = base_form.findField('LpuSection_did').getValue();
											if(newValue){
												if( !kemNapravlen.getValue()){
													kemNapravlen.setValue('1');
													kemNapravlen.automaticallyStamped = true; // поставим флаг, что установили автоматически значение
												}
											}else if(kemNapravlen.automaticallyStamped && !otdelenie){
												kemNapravlen.clearValue();
											}
											setLpuSectionGlobalStoreFilter({
												LpuSection_id: combo.getFieldValue('LpuSection_id')
											});
											base_form.findField('LpuSection_did').getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));
										}.createDelegate(this)
									}
								}]
							}]
						},{
							displayField: 'Org_Name',
							editable: false,
							enableKeyEvents: true,
							fieldLabel: 'Организация',
							hiddenName: 'Org_did',
							listeners: {
								'keyup': function(inp, e) {
									// alert('keyup');
								},
								'change': function(combo, newValue) {
									// alert('change'); return false;
								}
							},
							mode: 'local',
							onTrigger1Click: function() {
								var base_form = this.findById('EvnUslugaParSearchFilterForm').getForm();
								var combo = base_form.findField('Org_did');

								var prehosp_direct_combo = base_form.findField('PrehospDirect_id');
								var prehosp_direct_id = prehosp_direct_combo.getValue();
								var record = prehosp_direct_combo.getStore().getById(prehosp_direct_id);
								if ( !record || prehosp_direct_id == 1 || prehosp_direct_id == 7) {
									return false;
								}

								var prehosp_direct_code = record.get('PrehospDirect_Code');
								var org_type = '';

								switch ( prehosp_direct_code ) {
									case 2:
									case 5:
										org_type = 'lpu';
										break;

									case 4:
										org_type = 'military';
										break;

									case 3:
									case 6:
										org_type = 'org';
										break;

									default:
										org_type = 'org';
										break;
								}

								getWnd('swOrgSearchWindow').show({
									object: org_type,
									onClose: function() {
										combo.focus(true, 200)
									},
									onSelect: function(org_data) {
										// var combo = this;
										if ( org_data.Org_id > 0 ) {
											combo.getStore().loadData([{
												Org_id: org_data.Org_id,
												Lpu_id: org_data.Lpu_id,
												Org_Name: org_data.Org_Name
											}]);

											combo.setValue(org_data.Org_id);
											combo.fireEvent('change', combo, combo.getValue())

											getWnd('swOrgSearchWindow').hide();
											combo.collapse();
										}
									}
								});
							}.createDelegate(this),
							store: new Ext.data.JsonStore({
								autoLoad: false,
								fields: [
									{name: 'Org_id', type: 'int'},
									{name: 'Lpu_id', type: 'int'},
									{name: 'Org_Name', type: 'string'}
								],
								key: 'Org_id',
								sortInfo: {
									field: 'Org_Name'
								},
								url: C_ORG_LIST
							}),
							tabIndex: TABINDEX_EUPSW + 75,
							tpl: new Ext.XTemplate(
								'<tpl for="."><div class="x-combo-list-item">',
								'{Org_Name}',
								'</div></tpl>'
							),
							trigger1Class: 'x-form-search-trigger',
							triggerAction: 'none',
							valueField: 'Org_id',
							width: 500,
							xtype: 'swbaseremotecombo'
						}]
					}, {
						autoHeight: true,
						labelWidth: 130,
						style: 'padding: 0px;',
						title: lang['mesto_vyipolneniya'],
						width: 755,
						xtype: 'fieldset',

						items: [{
							border: false,
							layout: 'form',
							items: [{
								fieldLabel: langs('МО'),
								hiddenName: 'EUPSWLpu_id',
								id: 'EUPSW_LpuCombo',
								lastQuery: '',
								tabIndex: TABINDEX_EUPSW + 72.5,
								listWidth: 535,
								width: 535,
								xtype: 'swlpucombo'
							}]
						}, {
							border: false,
							layout: 'column',
							items: [{
								border: false,
								layout: 'form',
								items: [{
									hiddenName: 'LpuSection_uid',
									id: 'EUPSW_LpuSectionCombo',
									lastQuery: '',
									linkedElements: [
										'EUPSW_MedStaffFactCombo'
									],
									tabIndex: TABINDEX_EUPSW + 72,
									listWidth: 600,
									width: 200,
									xtype: 'swlpusectionglobalcombo'
								}]
							}, {
								border: false,
								layout: 'form',
								items: [{
									fieldLabel: lang['vrach'],
									hiddenName: 'MedStaffFact_uid',
									id: 'EUPSW_MedStaffFactCombo',
									listWidth: 600,
									parentElementId: 'EUPSW_LpuSectionCombo',
									tabIndex: TABINDEX_EUPSW + 73,
									width: 200,
									xtype: 'swmedstafffactglobalcombo'
								}]
							}]
						}]
					}, /*{
						fieldLabel: lang['usluga'],
						hiddenName: 'Usluga_id',
						listWidth: 600,
						tabIndex: TABINDEX_EUPSW + 74,
						width: 450,
						xtype: 'swuslugacombo'
					},{
						allowBlank: true,
						value: null,
						fieldLabel: lang['kompleksnaya_usluga'],
						name: 'UslugaComplex_id',
						listWidth: 600,
						tabIndex: TABINDEX_EUPSW + 75,
						width: 450,
						xtype: 'swuslugacomplexpidcombo',
						listeners: {
							'render': function(combo){
							combo.getStore().removeAll();
							combo.getStore().load({});
							}
						}
					},*/
					{
						border: false,
						layout: 'column',
						items: [{
							border: false,
							layout: 'form',
							items: [
								{
									fieldLabel: langs('Категория услуги'),
									hiddenName: 'UslugaCategory_id',
									listeners: {
										'select': function (combo, record) {
											var base_form = this.findById('EvnUslugaParSearchFilterForm').getForm();

											base_form.findField('UslugaComplex_id').clearValue();
											base_form.findField('UslugaComplex_id').getStore().removeAll();

											if ( !record ) {
												base_form.findField('UslugaComplex_id').setUslugaCategoryList();
												return false;
											}


											base_form.findField('UslugaComplex_id').setUslugaCategoryList([ record.get('UslugaCategory_SysNick') ]);

											return true;
										}.createDelegate(this)
									},
									loadParams: (getRegionNick() == 'kz' ? {params: {where: "where UslugaCategory_SysNick in ('classmedus')"}} : null),
									tabIndex: TABINDEX_EUCOMEF + 9,
									width: 200,
									xtype: 'swuslugacategorycombo'
								},
								{
									fieldLabel: langs('В составе исследования'),
									hiddenName: 'Part_of_the_study',
									id: 'Part_of_the_study',
									width: 200,
									xtype: 'checkbox'
								}
							]
						}, {
							border: false,
							layout: 'form',
							labelWidth: 150,
							items: [
								{
									comboSubject: 'UslugaExecutionType',
									xtype: 'swcommonsprcombo',
									hiddenName: 'UslugaExecutionType_id',
									valueField: 'UslugaExecutionType_id',
									showCodefield: false,
									fieldLabel: langs('Результат выполнения'),
									onLoadStore: function (store) {

										if (store.find('UslugaExecutionType_id', 4) == -1)
										{
											store.loadData([ {
												UslugaExecutionType_Code: 4,
												UslugaExecutionType_Name: langs('Без результата'),
												UslugaExecutionType_id: 4
											}], true);
										}

										return true;
									},
									tpl: new Ext.XTemplate('<tpl for="."><div class="x-combo-list-item">{UslugaExecutionType_Name}&nbsp;</div></tpl>'),
									editable: false
								}
							]
						}]
					}, {
						fieldLabel: lang['usluga'],
						hiddenName: 'UslugaComplex_id',
						listWidth: 700,
						tabIndex: TABINDEX_EUCOMEF + 10,
						width: 450,
						xtype: 'swuslugacomplexnewcombo'
					}, {
						border: false,
						layout: 'column',
						items: [{
							border: false,
							layout: 'form',
							items: [{
								fieldLabel: lang['data_okazaniya'],
								name: 'EvnUslugaPar_setDate_Range',
								plugins: [
									new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)
								],
								tabIndex: TABINDEX_EUPSW + 76,
								width: 200,
								xtype: 'daterangefield'
							}]
						}, {
							border: false,
							layout: 'form',
							items: [{
								useCommonFilter: true,
								listWidth: 300,
								tabIndex: TABINDEX_EUPSW + 77,
								width: 200,
								xtype: 'swpaytypecombo'
							}]
						}]
					}, {
						border: false,
						layout: 'column',
						items: [{
							border: false,
							layout: 'form',
							items: [{
								fieldLabel: 'Услуга передана в АИС-Пол-ка (25-5у)',
								hiddenName: 'toAis25',
								tabIndex: TABINDEX_EPLSTOMSW + 98,
								width: 200,
								hidden: hiddenToAis25,
								hideLabel: hiddenToAis25,
								xtype: 'swyesnocombo'
							}]
						}, {
							border: false,
							layout: 'form',
							items: [{
								fieldLabel: 'Услуга передана в АИС-Пол-ка (25-9у)',
								hiddenName: 'toAis259',
								tabIndex: TABINDEX_EPLSTOMSW + 98,
								width: 200,
								hidden: hiddenToAis259,
								hideLabel: hiddenToAis259,
								xtype: 'swyesnocombo'
							}]
						}]
					}]
				}]
			}),
			new sw.Promed.ViewFrame({
				useArchive: 1,
				actions: [
					{
						name: 'action_add',
						handler: function() { this.openEvnUslugaParEditWindow('add'); }.createDelegate(this),
						hidden: !getRegionNick().inlist(['perm','ekb','kareliya'])
					},
					{
						name: 'action_edit',
						handler: function() { this.openEvnUslugaParEditWindow('edit'); }.createDelegate(this),
						hidden: !getRegionNick().inlist(['perm','ekb','krym','kareliya','vologda'])
					},
					{ name: 'action_view', handler: function() { this.openEvnUslugaParEditWindow('view'); }.createDelegate(this) },
					{
						name: 'action_delete',
						handler: function() { this.deleteEvnUslugaPar(); }.createDelegate(this)
					},
					{ name: 'action_refresh', handler: function() { this.findById('EUPSW_EvnUslugaParSearchGrid').getGrid().getStore().reload(); }.createDelegate(this) },
					{ name: 'action_print',
						menuConfig: {
							printCost: {name: 'printCost', hidden: ! getRegionNick().inlist(['perm']), text: langs('Справка о стоимости лечения'), handler: function () { win.printCost() }}
						}
					}
				],
				autoExpandColumn: 'autoexpand',
				autoExpandMin: 150,
				autoLoadData: false,
				dataUrl: C_SEARCH,
				id: 'EUPSW_EvnUslugaParSearchGrid',
				pageSize: 100,
				paging: true,
				region: 'center',
				root: 'data',
				stringfields: [
					{ name: 'EvnUslugaPar_id', type: 'int', header: 'ID', key: true },
					{ name: 'accessType', type: 'string', hidden: true },
					{ name: 'EvnUslugaPar_IsSigned', type: 'int', hidden: true },
					{ name: 'EvnXml_id', type: 'int', hidden: true },
					{ name: 'Person_id', type: 'int', hidden: true },
					{ name: 'PersonEvn_id', type: 'int', hidden: true },
					{ name: 'Server_id', type: 'int', hidden: true },
					{ name: 'AisError', type: 'string', hidden: true },
					{ name: 'Person_Surname', type: 'string', header: langs('Фамилия'), width: 150 },
					{ name: 'Person_Firname', type: 'string', header: langs('Имя'), width: 150 },
					{ name: 'Person_Secname', type: 'string', header: langs('Отчество'), width: 150 },
					{ name: 'Person_Birthday', type: 'date', format: 'd.m.Y', header: langs('Д/р'), width: 100 },
					{ name: 'Person_deadDT', type: 'date', format: 'd.m.Y', header: langs('Дата смерти'), width: 100 },
					{ name: 'EvnUslugaPar_setDate', type: 'date', format: 'd.m.Y', header: langs('Дата'), width: 100 },
					{ name: 'Referral_Org_Nick', type: 'string', header: langs('Направившая МО'), width: 150, hidden: !( getRegionNick().inlist(['vologda']) )},
					{ name: 'Lpu_Name', type: 'string', header: langs('МО'), width: 350, hidden: !(getRegionNick().inlist(['penza']) || isUserGroup('OuzSpec'))},
					{ name: 'LpuSection_Name', type: 'string', header: langs('Отделение'), width: 150 },
					{ name: 'MedPersonal_Fio', type: 'string', header: langs('Врач'), width: 150 },
					{ name: 'Usluga_Code', type: 'string', header: langs('Код услуги'), width: 100 },
					{ name: 'Usluga_Name', header: langs('Наименование услуги'), id: 'autoexpand', renderer: function(value, cellEl, rec){
						if (Ext.isEmpty(rec.get('EvnXml_id'))) {
							return value;
						} else {
							return "<a href='#' onClick='getWnd(\"swEvnXmlViewWindow\").show({ EvnXml_id: " + rec.get('EvnXml_id') + " });'>"+value+"</a>";
						}
					}},
					{ name: 'PayType_Name', type: 'string', header: langs('Вид оплаты'), width: 100 },
					{ name: 'EvnCostPrint_setDT', type: 'date', header: langs('Дата выдачи справки/отказа'), width: 150, hidden: ! getRegionNick().inlist(['perm', 'kz', 'ufa']) },
					{ name: 'EvnCostPrint_IsNoPrintText', type: 'string', header: langs('Справка о стоимости лечения'), width: 150, hidden: ! getRegionNick().inlist(['perm', 'kz', 'ufa']) },
					{ name: 'toAis25', type: 'checkcolumn', header: 'АИС Пол-ка (25-5у)', width: 150, hidden: hiddenToAis25 },
					{ name: 'toAis259', type: 'checkcolumn', header: 'АИС Пол-ка (25-9у)', width: 150, hidden: hiddenToAis259 },
				],
				toolbar: true,
				totalProperty: 'totalCount', 
				onBeforeLoadData: function() {
					this.getButtonSearch().disable();
				}.createDelegate(this),
				onLoadData: function() {
					this.getButtonSearch().enable();
				}.createDelegate(this),
				onRowSelect: function(sm, index, record) {
					// Запретить редактирование/удаление архивных записей
					if(win.viewOnly == true){
						this.getAction('action_add').setDisabled(true);
						this.getAction('action_edit').setDisabled(true);
						this.getAction('action_view').setDisabled(false);
						this.getAction('action_delete').setDisabled(true);
					}
					else{
						if (getGlobalOptions().archive_database_enable) {
							this.getAction('action_edit').setDisabled(record.get('accessType') == 'view' || record.get('archiveRecord') == 1);
							this.getAction('action_delete').setDisabled(record.get('accessType') == 'view' || record.get('archiveRecord') == 1);
						} else {
							var actionEdit = ( record.get('accessType') == 'view' || record.get('EvnUslugaPar_IsSigned') == 2 ) ? true : false;
							this.getAction('action_edit').setDisabled(actionEdit);
							this.getAction('action_delete').setDisabled(record.get('accessType') == 'view');
						}
					}
				}
			})]
		});

		sw.Promed.swEvnUslugaParSearchWindow.superclass.initComponent.apply(this, arguments);
	},
	keys: [{
		fn: function(inp, e) {
			var current_window = Ext.getCmp('EvnUslugaParSearchWindow');

			switch ( e.getKey() ) {
				case Ext.EventObject.INSERT:
					current_window.openEvnUslugaParEditWindow('add');
				break;
			}
		},
		key: [
			Ext.EventObject.INSERT
		],
		stopEvent: true
	}, {
		alt: true,
		fn: function(inp, e) {
			var current_window = Ext.getCmp('EvnUslugaParSearchWindow');
			var search_filter_tabbar = current_window.findById('EUPSW_SearchFilterTabbar');

			switch ( e.getKey() ) {
				case Ext.EventObject.C:
					current_window.doReset();
				break;

				case Ext.EventObject.J:
					current_window.hide();
				break;

				case Ext.EventObject.NUM_ONE:
				case Ext.EventObject.ONE:
					search_filter_tabbar.setActiveTab(0);
				break;

				case Ext.EventObject.NUM_TWO:
				case Ext.EventObject.TWO:
					search_filter_tabbar.setActiveTab(1);
				break;

				case Ext.EventObject.NUM_THREE:
				case Ext.EventObject.THREE:
					search_filter_tabbar.setActiveTab(2);
				break;

				case Ext.EventObject.NUM_FOUR:
				case Ext.EventObject.FOUR:
					search_filter_tabbar.setActiveTab(3);
				break;

				case Ext.EventObject.NUM_FIVE:
				case Ext.EventObject.FIVE:
					search_filter_tabbar.setActiveTab(4);
				break;

				case Ext.EventObject.NUM_SIX:
				case Ext.EventObject.SIX:
					search_filter_tabbar.setActiveTab(5);
				break;
			}
		},
		key: [
			Ext.EventObject.C,
			Ext.EventObject.FIVE,
			Ext.EventObject.FOUR,
			Ext.EventObject.J,
			Ext.EventObject.NUM_FIVE,
			Ext.EventObject.NUM_FOUR,
			Ext.EventObject.NUM_ONE,
			Ext.EventObject.NUM_SIX,
			Ext.EventObject.NUM_TWO,
			Ext.EventObject.NUM_THREE,
			Ext.EventObject.ONE,
			Ext.EventObject.SIX,
			Ext.EventObject.TWO,
			Ext.EventObject.THREE
		],
		stopEvent: true
	}],
	layout: 'border',
	listeners: {
		'hide': function(win) {
			win.doReset();
		},
		'maximize': function(win) {
			win.findById('EvnUslugaParSearchFilterForm').doLayout();
		},
		'restore': function(win) {
			win.findById('EvnUslugaParSearchFilterForm').doLayout();
		},
                'resize': function (win, nW, nH, oW, oH) {
//                    log(nW);
                    win.findById('EUPSW_SearchFilterTabbar').setWidth(nW - 5);
                    win.findById('EvnUslugaParSearchFilterForm').setWidth(nW - 5);
                }
	},
	maximizable: true,
	minHeight: 550,
	minWidth: 800,
	modal: false,
	openEvnUslugaParEditWindow: function(action) {
		if ( action != 'add' && action != 'edit' && action != 'view' ) {
			return false;
		}

		if (!getRegionNick().inlist(['perm','ekb','krym','kareliya','vologda']) && action == 'edit') {
			action = 'view';
		}

		if ( action == 'add' && getWnd('swPersonSearchWindow').isVisible() ) {
			sw.swMsg.alert(lang['soobschenie'], lang['okno_poiska_cheloveka_uje_otkryito']);
			return false;
		}

		if ( getWnd('swEvnUslugaParEditWindow').isVisible() ) {
			sw.swMsg.alert(lang['soobschenie'], lang['okno_redaktirovaniya_paraklinicheskoy_uslugi_uje_otkryito']);
			return false;
		}

		var params = new Object();
		var grid = this.findById('EUPSW_EvnUslugaParSearchGrid').getGrid();
		var _that = this;
		params.action = action;
		params.callback = function(data) {
			if ( !data || !data.evnUslugaData ) {
				return false;
			}

			// Обновить запись в grid
			var record = grid.getStore().getById(data.evnUslugaData.EvnUslugaPar_id);

			if ( !record ) {
				if ( grid.getStore().getCount() == 1 && !grid.getStore().getAt(0).get('EvnUslugaPar_id') ) {
					grid.getStore().removeAll();
				}

				grid.getStore().loadData({ data: [ data.evnUslugaData ]}, true);
			}
			else {
				var evn_usluga_par_fields = new Array();

				grid.getStore().fields.eachKey(function(key, item) {
					evn_usluga_par_fields.push(key);
				});

				for ( i = 0; i < evn_usluga_par_fields.length; i++ ) {
					record.set(evn_usluga_par_fields[i], data.evnUslugaData[evn_usluga_par_fields[i]]);
				}

				record.commit();
			}

			grid.getStore().each(function(record) {
				if ( record.get('Person_id') == data.evnUslugaData.Person_id ) {
					record.set('Person_Birthday', data.evnUslugaData.Person_Birthday);
					record.set('Person_Surname', data.evnUslugaData.Person_Surname);
					record.set('Person_Firname', data.evnUslugaData.Person_Firname);
					record.set('Person_Secname', data.evnUslugaData.Person_Secname);
					record.set('Server_id', data.evnUslugaData.Server_id);

					record.commit();
				}
			});
		};

		if ( action == 'add' ) {
			getWnd('swPersonSearchWindow').show({
				onClose: Ext.emptyFn,
				onSelect: function(person_data) {
					var form_params = new Object();

					form_params.EvnUslugaPar_Kolvo = 1;
					form_params.EvnUslugaPar_setDate = undefined;
					form_params.EvnUslugaPar_setTime = undefined;
					form_params.LpuSection_did = undefined;
					form_params.LpuSection_uid = undefined;
					form_params.MedStaffFact_did = undefined;
					form_params.MedStaffFact_uid = undefined;
					form_params.Org_did = undefined;
					form_params.PayType_id = undefined;
					form_params.PrehospDirect_id = undefined;
					form_params.Usluga_id = undefined;

					Ext.apply(form_params, params);
					// TODO: Продумать использование getWnd в таких случаях
					// Явно так обращаться к данным не совсем правильно
					if ( getWnd('swPersonSearchWindow').formParams.EvnUslugaPar_Kolvo ) {
						form_params.EvnUslugaPar_Kolvo = getWnd('swPersonSearchWindow').formParams.EvnUslugaPar_Kolvo;
					}

					if ( getWnd('swPersonSearchWindow').formParams.EvnUslugaPar_setDate ) {
						form_params.EvnUslugaPar_setDate = getWnd('swPersonSearchWindow').formParams.EvnUslugaPar_setDate;
					}

					if ( getWnd('swPersonSearchWindow').formParams.EvnUslugaPar_setTime ) {
						form_params.EvnUslugaPar_setTime = getWnd('swPersonSearchWindow').formParams.EvnUslugaPar_setTime;
					}

					if ( getWnd('swPersonSearchWindow').formParams.LpuSection_did ) {
						form_params.LpuSection_did = getWnd('swPersonSearchWindow').formParams.LpuSection_did;
					}

					if ( getWnd('swPersonSearchWindow').formParams.LpuSection_uid ) {
						form_params.LpuSection_uid = getWnd('swPersonSearchWindow').formParams.LpuSection_uid;
					}

					if ( getWnd('swPersonSearchWindow').formParams.MedStaffFact_did ) {
						form_params.MedStaffFact_did = getWnd('swPersonSearchWindow').formParams.MedStaffFact_did;
					}

					if ( getWnd('swPersonSearchWindow').formParams.MedStaffFact_uid ) {
						form_params.MedStaffFact_uid = getWnd('swPersonSearchWindow').formParams.MedStaffFact_uid;
					}

					if ( getWnd('swPersonSearchWindow').formParams.Org_did ) {
						form_params.Org_did = getWnd('swPersonSearchWindow').formParams.Org_did;
					}

					if ( getWnd('swPersonSearchWindow').formParams.PayType_id ) {
						form_params.PayType_id = getWnd('swPersonSearchWindow').formParams.PayType_id;
					}

					if ( getWnd('swPersonSearchWindow').formParams.PrehospDirect_id ) {
						form_params.PrehospDirect_id = getWnd('swPersonSearchWindow').formParams.PrehospDirect_id;
					}

					if ( getWnd('swPersonSearchWindow').formParams.Usluga_id ) {
						form_params.Usluga_id = getWnd('swPersonSearchWindow').formParams.Usluga_id;
					}

					form_params.onHide = function() {
						getWnd('swPersonSearchWindow').formParams = new Object();
						getWnd('swPersonSearchWindow').findById('person_search_form').getForm().findField('PersonSurName_SurName').focus(true, 250);
					};
					form_params.Person_Birthday = person_data.Person_Birthday;
					form_params.Person_Firname = person_data.Person_Firname;
					form_params.Person_id = person_data.Person_id;
					form_params.Person_Secname = person_data.Person_Secname;
					form_params.Person_Surname = person_data.Person_Surname;
					form_params.PersonEvn_id = person_data.PersonEvn_id;
					form_params.Server_id = person_data.Server_id;

					getWnd('swEvnUslugaParEditWindow').show(form_params);
				},
				personFirname: this.findById('EvnUslugaParSearchFilterForm').getForm().findField('Person_Firname').getValue(),
				personSecname: this.findById('EvnUslugaParSearchFilterForm').getForm().findField('Person_Secname').getValue(),
				personSurname: this.findById('EvnUslugaParSearchFilterForm').getForm().findField('Person_Surname').getValue(),
				searchMode: 'all',
				searchWindowOpenMode: 'EvnUslugaPar'
			});
		}
		else {
			if ( !grid.getSelectionModel().getSelected() ) {
				return false;
			}

			var selected_record = grid.getSelectionModel().getSelected();

			var evn_usluga_par_id = selected_record.get('EvnUslugaPar_id');
			var person_id = selected_record.get('Person_id');
			var server_id = selected_record.get('Server_id');

			if ( evn_usluga_par_id > 0 && person_id > 0 && server_id >= 0 ) {
				params.EvnUslugaPar_id = evn_usluga_par_id;
				params.onHide = function() {
					grid.getView().focusRow(grid.getStore().indexOf(selected_record));
				};
				params.Person_Birthday = selected_record.get('Person_Birthday');
				params.Person_Firname = selected_record.get('Person_Firname');
				params.Person_id = person_id;
				params.Person_Secname = selected_record.get('Person_Secname');
				params.Person_Surname = selected_record.get('Person_Surname');
				params.Server_id = server_id;
				if (getGlobalOptions().archive_database_enable) {
					params.archiveRecord = selected_record.get('archiveRecord');
				}
				params.viewOnly = _that.viewOnly;
				getWnd('swEvnUslugaParEditWindow').show(params);
			}
		}
	},
	plain: true,
	resizable: true,
	show: function() {
		sw.Promed.swEvnUslugaParSearchWindow.superclass.show.apply(this, arguments);

		this.LpuSection_id = null;
		this.viewOnly = false;
		if (arguments[0]) {
			if (arguments[0].LpuSection_id) {
				this.LpuSection_id = arguments[0].LpuSection_id;
			}
			if (arguments[0].viewOnly) {
				this.viewOnly = arguments[0].viewOnly;
			}
		}
		
		var base_form = this.findById('EvnUslugaParSearchFilterForm').getForm();

		this.restore();
		this.center();
		this.maximize();
		this.doReset();
		
		this.findById('EUPSW_EvnUslugaParSearchGrid').ViewActions.action_add.setDisabled(this.viewOnly);
		this.findById('EUPSW_EvnUslugaParSearchGrid').ViewActions.action_edit.setDisabled(this.viewOnly);
		this.findById('EUPSW_EvnUslugaParSearchGrid').ViewActions.action_delete.setDisabled(this.viewOnly);
		
		setLpuSectionGlobalStoreFilter();
		setMedStaffFactGlobalStoreFilter();

		base_form.findField('LpuSection_uid').getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));
		base_form.findField('MedStaffFact_uid').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));

		base_form.findField('LpuSection_did').getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));
		base_form.findField('MedStaffFact_did').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));

		this.findById('EUPSW_SearchFilterTabbar').setActiveTab(5);
		this.findById('EUPSW_SearchFilterTabbar').setActiveTab(0);

		if (!Ext.isEmpty(this.LpuSection_id)) {
			base_form.findField('LpuSection_uid').setValue(this.LpuSection_id);
			base_form.findField('LpuSection_uid').disable();
		} else {
			base_form.findField('LpuSection_uid').enable();
		}

		base_form.getEl().dom.action = "/?c=Search&m=printSearchResults";
		base_form.getEl().dom.method = "post";
		base_form.getEl().dom.target = "_blank";
		base_form.standardSubmit = true;

		if (getRegionNick() != 'penza' || !isUserGroup('OuzSpec')) {
			this.findById('EUPSW_LpuCombo').hideContainer();
			this.findById('EUPSW_LpuCombo').setDisabled(true);
		}

		if ( !this.findById('EUPSW_EvnUslugaParSearchGrid').getAction('action_aislog') && getRegionNick() == 'kz' ) {
			this.findById('EUPSW_EvnUslugaParSearchGrid').addActions({
				handler: function() {
					var grid = this.findById('EUPSW_EvnUslugaParSearchGrid').getGrid();
					if ( !grid || !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('EvnUslugaPar_id') ) {
						return false;
					}
					var record = grid.getSelectionModel().getSelected();
					getWnd('swAisErrorLogWindow').show({Evn_id: record.get('EvnUslugaPar_id')});
				}.createDelegate(this),
				iconCls: 'actions16',
				id: 'EUPSW_action_aislog',
				name: 'action_aislog',
				text: 'Просмотреть ошибки ФЛК'
			});
		}
	},
	title: WND_PARKA_EUPSEARCH,
	width: 800
});
