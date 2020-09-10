/**
* swPersonDispSearchWindow - окно поиска по диспансерному учету.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Polka
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Pshenicyn Ivan aka IVP (ipshon@rambler.ru)
* @version      25.06.2009
* tabIndex: TABINDEX_PERSDISPSW
*/

sw.Promed.swPersonDispSearchWindow = Ext.extend(sw.Promed.BaseForm, {
	searchInERDB: function() {
		var that = this;
		
		if ( getWnd('swPersonSearchWindow').isVisible() ) {
			sw.swMsg.alert(lang['soobschenie'], lang['okno_poiska_cheloveka_uje_otkryito']);
			return false;
		}
		
		if ( getWnd('swPersonDispEditWindow').isVisible() ) {
			sw.swMsg.alert(lang['soobschenie'], lang['okno_redaktirovaniya_dispansernoy_kartyi_patsienta_uje_otkryito']);
			return false;
		}
		
		getWnd('swPersonSearchWindow').show({
			onSelect: function(person_data) {
				if (Ext.isEmpty(person_data.Person_Inn)){
					sw.swMsg.alert(lang['soobschenie'], 'У пациента не заполнено поле ИИН. Поиск невозможен.');
				} else {
					var myMask = new Ext.LoadMask(Ext.getBody(), {msg:"Поиск информации по диспансерным больным."});
					myMask.show();
					Ext.Ajax.request({
						url: '/?c=Erdb&m=getHuman',
						params: {
							Person_Inn: person_data.Person_Inn
						},
						callback: function(opt,suc,res) {
							myMask.hide();
							var result = Ext.util.JSON.decode(res.responseText);
							
							if (!result.success) {
								var msg = 'В какой-то момент, где-то, произошло что-то чего не должно было произойти.';
								if (result.message) {
									msg = result.message;
								} else if (result.Error_Msg) {
									msg = result.Error_Msg;
								}
								sw.swMsg.alert(lang['soobschenie'], msg);
								return false;
							}
							
							if (result.count == 0) {
								sw.swMsg.alert(lang['soobschenie'], 'У человека нет карт диспансерного учета.');
							} else {
								var params = {};
								params.isERDB = 1;
								params.HumanUID = result.all.GetHumanResult.Human.Human.UID;
								params.formParams = {
									Person_id: person_data.Person_id,
									PersonEvn_id: person_data.PersonEvn_id,
									Server_id: person_data.Server_id
								};
								
								if (result.count > 1) {
									params.DispCards = result.DispCards;
									getWnd('swPersonDispSelect').show(params);
								} else {
									params.action = result.DispCards[0].action;
									if (result.DispCards[0].action != 'add') params.formParams.PersonDisp_id = result.DispCards[0].PersonDisp_id;
									params.Nomkart = result.DispCards[0].Nomkart;
									params.Dt_beg = result.DispCards[0].Dt_beg;
									params.Dt_end = result.DispCards[0].Dt_end;
									params.Diag_id = result.DispCards[0].Diag_id;
									params.Dgroup_kod = (result.DispCards[0].Dgroup_kod)?result.DispCards[0].Dgroup_kod.Kod:null;
									params.Prich_End_ID = (result.DispCards[0].Prich_End_ID)?result.DispCards[0].Prich_End_ID.ID:null;
									params.Vra_UID_MedStaffFact_id = (result.DispCards[0].Vra_UID_MedStaffFact_id)?result.DispCards[0].Vra_UID_MedStaffFact_id:null;
									params.Vra_UID_LpuSection_id = (result.DispCards[0].Vra_UID_LpuSection_id)?result.DispCards[0].Vra_UID_LpuSection_id:null;
									params.PersonDispHist_MedPersonalFio = (result.DispCards[0].PersonDispHist_MedPersonalFio)?result.DispCards[0].PersonDispHist_MedPersonalFio:null;
									getWnd('swPersonDispEditWindow').show(params);
								}
							}
						}
					});
				}
			}.createDelegate(this),
			searchMode: 'all'
		});
	},
	
	addPersonDisp: function() {
		if ( getWnd('swPersonSearchWindow').isVisible() ) {
			sw.swMsg.alert(lang['soobschenie'], lang['okno_poiska_cheloveka_uje_otkryito']);
			return false;
		}

		if ( getWnd('swPersonDispEditWindow').isVisible() ) {
			sw.swMsg.alert(lang['soobschenie'], lang['okno_redaktirovaniya_dispansernoy_kartyi_patsienta_uje_otkryito']);
			return false;
		}
		
		var params = new Object();
		var formParams = new Object();
		
		params.action = 'add';
		if(this.ARMType){
			params.ARMType = this.ARMType;
		}
		params.callback = function() {
			this.refreshPersonDispSearchGrid();
		}.createDelegate(this);
		params.onHide = function() {
			getWnd('swPersonSearchWindow').findById('person_search_form').getForm().findField('PersonSurName_SurName').focus(true, 500);
		}.createDelegate(this);
						
		getWnd('swPersonSearchWindow').show({
			onClose: function() {
				// this.refreshPersonDispSearchGrid();
			}.createDelegate(this),
			onSelect: function(person_data) {
				formParams.Person_id = person_data.Person_id;
				formParams.PersonEvn_id = person_data.PersonEvn_id;
				formParams.Server_id = person_data.Server_id;

				params.formParams = formParams;

				getWnd('swPersonDispEditWindow').show(params);
			}.createDelegate(this),
			searchMode: 'all'
		});
	},
	buttonAlign: 'left',
	doResetAll: function() {
		var form = this.findById('PersonDispViewFilterForm');
		
		if ( form.getForm().findField('PersonCardStateType_id') && form.getForm().findField('PersonCardStateType_id').rendered )
			var pcst = form.getForm().findField('PersonCardStateType_id').getValue();			
		if ( form.getForm().findField('ViewAll_id') && form.getForm().findField('ViewAll_id').rendered )
			var va = form.getForm().findField('ViewAll_id').getValue();
		if ( form.getForm().findField('PrivilegeStateType_id') && form.getForm().findField('PrivilegeStateType_id').rendered )
			var pst = form.getForm().findField('PrivilegeStateType_id').getValue();
			
		form.getForm().reset();
		
		if ( form.getForm().findField('PersonCardStateType_id') && form.getForm().findField('PersonCardStateType_id').rendered )
			form.getForm().findField('PersonCardStateType_id').setValue(pcst);
		if ( form.getForm().findField('ViewAll_id') && form.getForm().findField('ViewAll_id').rendered )
			form.getForm().findField('ViewAll_id').setValue(va);
		if ( form.getForm().findField('PrivilegeStateType_id') && form.getForm().findField('PrivilegeStateType_id').rendered )
			form.getForm().findField('PrivilegeStateType_id').setValue(pst);
		
		var grid = this.findById('PersonDispSearchGrid').ViewGridPanel;
		grid.getStore().removeAll();
		this.findById('PersonDispSearchGrid').addEmptyRecord(this.findById('PersonDispSearchGrid').getGrid().getStore());
	},
	editPersonDisp: function() {
		var grid = this.findById('PersonDispSearchGrid').ViewGridPanel;
		var wnd = this;
		var current_row = grid.getSelectionModel().getSelected();

		if ( !current_row ) {
			return;
		}

		if ( getWnd('swPersonDispEditWindow').isVisible() ) {
			sw.swMsg.alert(lang['soobschenie'], lang['okno_redaktirovaniya_dispansernoy_kartyi_patsienta_uje_otkryito']);
			return false;
		}
		
		var formParams = new Object();
		var params = new Object();

		formParams.Person_id = current_row.data.Person_id;
		formParams.PersonDisp_id = current_row.data.PersonDisp_id;
		formParams.Server_id = current_row.data.Server_id;

		params.action = 'edit';
		if(this.ARMType){
			params.ARMType = this.ARMType;
		}
		params.callback = function() {
			Ext.getCmp('PersonDispSearchGrid').ViewActions.action_refresh.execute();
		}.createDelegate(this);
		params.formParams = formParams;
		params.onHide = function() {
			//
		}.createDelegate(this);
		
		if ( current_row.get('IsOurLpu') == 1 ) {
			params.action = 'view';
		}
		
		getWnd('swPersonDispEditWindow').show(params);
	},
	closable: true,
	closeAction: 'hide',
	collapsible: true,
	draggable: true,
	deletePersonDisp: function() {
		var current_window = this;
		var grid = current_window.findById('PersonDispSearchGrid').ViewGridPanel;
		var current_row = grid.getSelectionModel().getSelected();
		if (!current_row)
			return;
		if (getWnd('swPersonDispEditWindow').isVisible())
		{
			current_window.showMessage(lang['soobschenie'], lang['okno_redaktirovaniya_dispansernoy_kartyi_patsienta_uje_otkryito']);
			return false;
		}
		sw.swMsg.show({
			title: lang['podtverjdenie_udaleniya'],
			msg: lang['vyi_deystvitelno_jelaete_udalit_etu_zapis'],
			buttons: Ext.Msg.YESNO,
			fn: function ( buttonId ) {
				if ( buttonId == 'yes' )
				{
					Ext.Ajax.request({
						url: '?c=PersonDisp&m=deletePersonDisp',
						params: {PersonDisp_id: current_row.data.PersonDisp_id},
						callback: function() {
							current_window.doSearch();
						}
					});
				}
			}
		});
	},
	getRecordsCount: function() {
		var current_window = this;

		var form = current_window.findById('PersonDispViewFilterForm');

		if ( !form.getForm().isValid() ) {
			sw.swMsg.alert(lang['poisk_po_dispansernomu_uchetu'], lang['proverte_pravilnost_zapolneniya_poley_na_forme_poiska']);
			return false;
		}

		var loadMask = new Ext.LoadMask(Ext.get('PersonDispSearchWindow'), { msg: "Подождите, идет подсчет записей..." });
		loadMask.show();

		var post = getAllFormFieldValues(form);

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
	print030: function(){
		var current_window = this;
		var grid = current_window.findById('PersonDispSearchGrid').ViewGridPanel;
		var current_row = grid.getSelectionModel().getSelected();
		if (!current_row || !current_row.get('PersonDisp_id')){
			sw.swMsg.alert(lang['oshibka'], 'Не выбрана запись');
			return;
		}
		printBirt({
			'Report_FileName': 'f030_4u.rptdesign',
			'Report_Params': '&paramPersonDisp=' + current_row.get('PersonDisp_id'),
			'Report_Format': 'pdf'
		});
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
		var grid = this.findById('PersonDispSearchGrid').ViewGridPanel;
		var form = this.findById('PersonDispViewFilterForm');
		
		if ( form.isEmpty() ) {
			sw.swMsg.alert(lang['oshibka'], lang['ne_zapolneno_ni_odno_pole'], function() {
			});
			thisWindow.searchInProgress = false;
			return false;
		}

		form.getForm().findField('DispMedPersonal_id').setValue(form.getForm().findField('DispMedStaffFact_id').getFieldValue('MedPersonal_id'));
		form.getForm().findField('HistMedPersonal_id').setValue(form.getForm().findField('HistMedStaffFact_id').getFieldValue('MedPersonal_id'));
		
		var params = form.getForm().getValues();
		var arr = form.find('disabled', true);
		for (i = 0; i < arr.length; i++)
		{
			if (arr[i].getValue)
				params[arr[i].hiddenName] = arr[i].getValue();
		}
		
		if ( soc_card_id )
		{
			var params = {
				soc_card_id: soc_card_id,
				SearchFormType: params.SearchFormType
			};
		}
		
		params.start = 0;
		params.limit = 100;
		grid.getStore().removeAll();
		grid.getStore().baseParams = params;
		grid.getStore().load({
			params: params,
			callback: function (){
				thisWindow.searchInProgress = false;
			}
		});
	},
	height: 550,
	id: 'PersonDispSearchWindow',
	getButtonSearch: function() {
		// TODO: правильно юзать scope кнопки
		return Ext.getCmp('PDSW_SearchButton');
	},
	initComponent: function() {
		var win = this;

		Ext.apply(this, {
		buttons: [{
			handler: function() {
				this.ownerCt.doSearch();
			},
			iconCls: 'search16',
			id: 'PDSW_SearchButton',
			tabIndex: TABINDEX_PERSDISPSW + 97,
			text: BTN_FRMSEARCH
		}, {
			handler: function() {
				this.ownerCt.doResetAll();
			},
			iconCls: 'resetsearch16',
			tabIndex: TABINDEX_PERSDISPSW + 98,
			text: lang['cbros']
		}, {
			handler: function() {
				this.ownerCt.findById('PersonDispViewFilterForm').getForm().submit();
			}.createDelegate(this),
			iconCls: 'print16',
			tabIndex: TABINDEX_PERSDISPSW + 98,
			text: lang['pechat_spiska']
		}, {
			handler: function() {
				this.getRecordsCount();
			}.createDelegate(this),
			tabIndex: TABINDEX_PERSDISPSW + 98,
			text: lang['pokazat_kolichestvo_zapisey']
		}, {
            handler: function() {
                var grid = this.findById('PersonDispSearchGrid').ViewGridPanel;
                var current_row = grid.getSelectionModel().getSelected();
                var paramPersonDisp = current_row.data.PersonDisp_id;
				printBirt({
					'Report_FileName': 'PersonDispCard.rptdesign',
					'Report_Params': '&paramPersonDisp=' + paramPersonDisp,
					'Report_Format': 'pdf'
				});
            }.createDelegate(this),
            tabIndex: TABINDEX_PERSDISPSW + 98,
            text: lang['pechat_kontrolnoy_kartyi_disp_nablyudeniya']
        },
		'-',
		HelpButton(this, -1),
		{
			handler: function() {
				this.ownerCt.hide();
			},
			iconCls: 'cancel16',
			tabIndex: TABINDEX_PERSDISPSW + 99,
			text: BTN_FRMCANCEL,
			onShiftTabAction: function () {
				this.ownerCt.buttons[1].focus();
			},
			onTabAction: function () {
				var current_window = this.ownerCt;
				current_window.findById('PersonDispFilterTabPanel').getActiveTab().fireEvent('activate', current_window.findById('PersonDispFilterTabPanel').getActiveTab());
			}
		}],
			items: [
				new Ext.Panel({
					region: 'center',
					layout: 'border',
					items: [
					getBaseSearchFiltersFrame({
						id: 'PersonDispViewFilterForm',
						tabGridId: 'PersonDispSearchGrid',
						ownerWindow: this,
						searchFormType: 'PersonDisp',
						tabIndexBase: TABINDEX_PERSDISPSW,
						tabPanelId: 'PersonDispFilterTabPanel',
						tabs: [{
							autoHeight: true,
							bodyStyle: 'margin-top: 5px;',
							border: false,
							labelWidth: 280,
							layout: 'form',
							listeners: {
								'activate': function(panel) {
									var form = this.findById('PersonDispViewFilterForm');

									if ( form.getForm().findField('DispLpuSection_id').getStore().getCount() == 0 ) {
										setLpuSectionGlobalStoreFilter();
										form.getForm().findField('DispLpuSection_id').getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));
									}

									/*if ( form.getForm().findField('DispMedStaffFact_id').getStore().getCount() == 0 ) {
										setMedStaffFactGlobalStoreFilter();
										form.getForm().findField('DispMedStaffFact_id').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));
									}*/
									
									if(form.getForm().findField('DispMedStaffFact_id').getStore().getCount() == 0  || form.getForm().findField('HistMedStaffFact_id').getStore().getCount() == 0)
									{
										setMedStaffFactGlobalStoreFilter();
										if(form.getForm().findField('DispMedStaffFact_id').getStore().getCount() == 0)
											form.getForm().findField('DispMedStaffFact_id').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));
										if(form.getForm().findField('HistMedStaffFact_id').getStore().getCount() == 0)
											form.getForm().findField('HistMedStaffFact_id').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));
									}

									if ( form.getForm().findField('ViewAll_id').getValue() == '' )
										form.getForm().findField('ViewAll_id').setValue(1);
									form.getForm().findField('ViewAll_id').focus(250, true);
								}.createDelegate(this)
							},
							title: lang['6_dispansernyiy_uchet'],
							items: [{
								name: 'DispMedPersonal_id',
								value: 0,
								xtype: 'hidden'
							},
							{
								name: 'HistMedPersonal_id',
								value: 0,
								xtype: 'hidden'
							},
							new sw.Promed.SwBaseLocalCombo({
								displayField: 'view_all_name',
								fieldLabel: lang['otobrajat_kartyi_du'],
								hiddenName: 'ViewAll_id',
								hideEmptyRow: true,
								id: 'PDSW_view_all_combo',
								store: new Ext.data.SimpleStore(
								{
									key: 'view_all_id',
									autoLoad: true,
									fields:
									[
										{name:'view_all_id', type:'int'},
										{name:'view_all_name', type:'string'}
									],
									data : [[1, lang['tolko_aktualnyie']], [2, lang['vklyuchaya_ne_aktualnyie']]]
								}),
								tabIndex: TABINDEX_PERSDISPSW + 56,
								value: 1,
								valueField: 'view_all_id',
								width: 200
							}), {
								hiddenName: 'DispLpuSection_id',
								id: 'PDSW_LpuSectionDispCombo',
								lastQuery: '',
								linkedElements: [
									'PDSW_MedPersonalCombo'
								],
								listWidth: 700,
								tabIndex: TABINDEX_PERSDISPSW + 57,
								width: 500,
								xtype: 'swlpusectionglobalcombo'
							}, {
								width: 500,
								autoLoad: false,
								listWidth: 700,
								hiddenName: 'DispLpuSectionProfile_id',
								id: 'PDSW_LpuSectionProfileDispCombo',
								lastQuery: '',
								xtype: 'swlpusectionprofilecombo'
							}, {
								hiddenName: 'DispMedStaffFact_id',
								id: 'PDSW_MedPersonalCombo',
								lastQuery: '',
								parentElementId: 'PDSW_LpuSectionDispCombo',
								fieldLabel: lang['postavivshiy_vrach'],
								listWidth: 700,
								tabIndex: TABINDEX_PERSDISPSW + 58,
								width: 500,
								xtype: 'swmedstafffactglobalcombo'
							}, 
							{
								hiddenName: 'HistMedStaffFact_id',
								id: 'PDSW_MedPersonalComboHist',
								lastQuery: '',
								fieldLabel: 'Ответственный врач',
								listWidth: 700,
								tabIndex: TABINDEX_PERSDISPSW + 58,
								width: 500,
								xtype: 'swmedstafffactglobalcombo',
								listeners: {
									'change': function(combo,value)
									{
										var form = this.findById('PersonDispViewFilterForm');
										var checkMPHistory = form.getForm().findField('checkMPHistory');
										if(!Ext.isEmpty(value) && value > 0)
										{
											checkMPHistory.enable();
										}
										else
										{
											checkMPHistory.disable();
											checkMPHistory.setValue(false);
										}
									}.createDelegate(this)
								}
							},
							{
								fieldLabel: 'Учитывать историю ответственных врачей',
								name: 'checkMPHistory',
								disabled: true,
								id: 'PDSW_checkMPHistory',
								xtype: 'checkbox',
								checked: false
							},
							{
								border: false,
								layout: 'column',
								items: [{
									border: false,
									layout: 'form',
									items: [{
										fieldLabel: lang['data_postanovki_na_uchet'],
										name: 'PersonDisp_begDate',
										plugins: [
											new Ext.ux.InputTextMask('99.99.9999', false)
										],
										tabIndex: TABINDEX_PERSDISPSW + 59,
										width: 100,
										xtype: 'swdatefield'
									}]
								}, {
									border: false,
									layout: 'form',
									labelWidth: 225,
									items: [{
										fieldLabel: lang['diapazon_dat_postanovki_na_uchet'],
										name: 'PersonDisp_begDate_Range',
										tabIndex: TABINDEX_PERSDISPSW + 60,
										plugins: [
											new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)
										],
										width: 170,
										xtype: 'daterangefield'
									}]
								}]
							},
							{
								border: false,
								layout: 'column',
								items: [{
									border: false,
									layout: 'form',
									items: [{
										fieldLabel: lang['data_sled_posescheniya'],
										name: 'PersonDisp_NextDate',
										tabIndex: TABINDEX_PERSDISPSW + 61,
										plugins: [
											new Ext.ux.InputTextMask('99.99.9999', false)
										],
										width: 100,
										xtype: 'swdatefield'
									}]
								}, {
									border: false,
									layout: 'form',
									labelWidth: 225,
									items: [{
										fieldLabel: lang['diapazon_dat_sled_posescheniya'],
										name: 'PersonDisp_NextDate_Range',
										tabIndex: TABINDEX_PERSDISPSW + 62,
										plugins: [
											new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)
										],
										width: 170,
										xtype: 'daterangefield'
									}]
								}]
							},
							{
								border: false,
								layout: 'column',
								items: [{
									border: false,
									layout: 'form',
									items: [{
										fieldLabel: lang['data_snyatiya_s_ucheta'],
										name: 'PersonDisp_endDate',
										tabIndex: TABINDEX_PERSDISPSW + 63,																				
										plugins: [
											new Ext.ux.InputTextMask('99.99.9999', false)
										],
										width: 100,
										xtype: 'swdatefield'
									}]
								}, {
									border: false,
									layout: 'form',
									labelWidth: 225,
									items: [{
										fieldLabel: lang['diapazon_dat_snyatiya_s_ucheta'],
										name: 'PersonDisp_endDate_Range',
										tabIndex: TABINDEX_PERSDISPSW + 64,																				
										plugins: [
											new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)
										],
										width: 170,
										xtype: 'daterangefield'
									}]
								}]
							}, {
								border: false,
								layout: 'column',
								items: [{
									border: false,
									layout: 'form',
									items: [{
										fieldLabel: lang['data_posledney_yavki'],
										name: 'PersonDisp_LastDate',
										tabIndex: TABINDEX_PERSDISPSW + 65,																				
										plugins: [
											new Ext.ux.InputTextMask('99.99.9999', false)
										],
										width: 100,
										xtype: 'swdatefield'
									}]
								}, {
									border: false,
									layout: 'form',
									labelWidth: 225,
									items: [{
										fieldLabel: 'Диапазон дат последней явки',
										name: 'PersonDisp_LastDate_Range',
										tabIndex: TABINDEX_PERSDISPSW + 66,																				
										plugins: [
											new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)
										],
										width: 170,
										xtype: 'daterangefield'
									}]
								}]
							}, {
								xtype: 'swdispouttypecombo',
								editable: false,
								hiddenName: 'DispOutType_id',
								codeField: 'DispOutType_Code',
								id: 'PDSW_DispOutTypeCombo',
								fieldLabel: lang['prichina_snyatiya_c_ucheta'],
								tabIndex: TABINDEX_PERSDISPSW + 67,																		
								tpl:
									'<tpl for="."><div class="x-combo-list-item">'+
									'<font color="red">{DispOutType_Code}</font>&nbsp;{DispOutType_Name}' +
									'</div></tpl>',
								width: 500
							}, { //https://redmine.swan.perm.ru/issues/72643
								fieldLabel: 'Закрыта автоматически',
								name: 'PersonDisp_IsAutoClose',
								tabIndex: this.tabIndexBase + 68,
								width: 180,
								xtype: 'checkbox'
							},
							{
								fieldLabel: lang['po_rez_dop_disp'],
								enableKeyEvents: true,
								hiddenName: 'DiagDetectType',
								id: 'PDSW_DiagDetectTypeCombo',
								listeners: {
									'keydown': function(inp, e) {
										if (!e.shiftKey && e.getKey() == e.TAB)
										{
											Ext.TaskMgr.start({
												run : function() {
													Ext.TaskMgr.stopAll();
													Ext.getCmp('PersonDispSearchGrid').focus();													
												},
												interval : 200
											});
											e.stopEvent();
										}
									}
								},
								tabIndex: TABINDEX_PERSDISPSW + 69,
								width: 100,
								xtype: 'swyesnocombo'
							}]
						},
						{
							autoHeight: true,
							bodyStyle: 'margin-top: 5px;',
							border: false,
							labelWidth: 150,
							layout: 'form',
							listeners: {
								'activate': function(panel) {
									this.findById('PersonDispViewFilterForm').getForm().findField('Sickness_id').focus(250, true);
								}.createDelegate(this)
							},								
							title: lang['7_dispansernyiy_uchet_diagnozyi'],
							items: [{
								codeField: 'Sickness_Code',
								displayField: 'Sickness_Name',
								editable: true,
								fieldLabel: lang['zabolevanie'],
								hiddenName: 'Sickness_id',
								id: 'PDSW_SicknessCombo',
								listeners: {
									'change': function(combo, newValue) {
										if ( newValue > 0 )
										{
											Ext.getCmp('PersonDispViewFilterForm').getForm().findField('Disp_Diag_id').clearValue();	
											Ext.getCmp('PersonDispViewFilterForm').getForm().findField('Disp_Diag_Code_From').clearValue();	
											Ext.getCmp('PersonDispViewFilterForm').getForm().findField('Disp_Diag_Code_To').clearValue();	
										}
									}.createDelegate(this)
								},
								store: new Ext.db.AdapterStore(
								{
									dbFile: 'Promed.db',
									tableName: 'Sickness',
									autoLoad: true,
									fields:
									[
										{name: 'Sickness_id',    type:'int'},
										{name: 'PrivilegeType_id',    type:'int'},
										{name: 'Sickness_Code',    type:'int'},
										{name: 'Sickness_Name',  type:'string'}
									],
									sortInfo:
									{
										field: 'Sickness_Code'
									}
								}),
								tabIndex: TABINDEX_PERSDISPSW + 70,
								tpl:
									'<tpl for="."><div class="x-combo-list-item">'+
									'<font color="red">{Sickness_Code}</font>&nbsp;{Sickness_Name}'+
									'</div></tpl>',
								valueField: 'Sickness_id',
								width: 500,
								xtype: 'swbaselocalcombo'
							}, {
								beforeBlur: function() {
									// медитируем
									return true;
								},
								hiddenName: 'Disp_Diag_id',
								id: 'PDSW_DiagCombo',
								listeners: {
									'select': function(combo, record, index) {
										combo.setRawValue(record.get('Diag_Code') + " " + record.get('Diag_Name'));
									},
									'change': function(combo, newValue) {
										if ( newValue > 0 )
											Ext.getCmp('PersonDispViewFilterForm').getForm().findField('Sickness_id').clearValue();	
									}.createDelegate(this)
								},								
								listWidth: 600,
								tabIndex: TABINDEX_PERSDISPSW + 71,
								width: 500,
								xtype: 'swdiagcombo'
							}, {
								border: false,
								layout: 'column',
								items: [{								
									border: false,
									layout: 'form',
									items: [{
										beforeBlur: function() {
											// медитируем
											return true;
										},
										fieldLabel: lang['diagnoz_s'],
										hiddenName: 'Disp_Diag_Code_From',
										listeners: {
											'select': function(combo, record, index) {
												combo.setRawValue(record.get('Diag_Code') + " " + record.get('Diag_Name'));
											},
											'change': function(combo, newValue) {
												if ( newValue != '' )
													Ext.getCmp('PersonDispViewFilterForm').getForm().findField('Sickness_id').clearValue();	
											}.createDelegate(this)
										},
										listWidth: 600,
										tabIndex: TABINDEX_PERSDISPSW + 72,
										valueField: 'Diag_Code',
										width: 233,
										xtype: 'swdiagcombo'
									}]
								}, {
									border: false,
									layout: 'form',
									labelWidth: 29,
									items: [{
										beforeBlur: function() {
											// медитируем
											return true;
										},
										fieldLabel: lang['po'],
										hiddenName: 'Disp_Diag_Code_To',
										listeners: {
											'select': function(combo, record, index) {
												combo.setRawValue(record.get('Diag_Code') + " " + record.get('Diag_Name'));
											},
											'change': function(combo, newValue) {
												if ( newValue != '' )
													Ext.getCmp('PersonDispViewFilterForm').getForm().findField('Sickness_id').clearValue();	
											}.createDelegate(this)
										},
										listWidth: 600,
										tabIndex: TABINDEX_PERSDISPSW + 73,
										valueField: 'Diag_Code',
										width: 233,
										xtype: 'swdiagcombo'
									}]
								}]
							}, {
								beforeBlur: function() {
									// медитируем
									return true;
								},
								fieldLabel: lang['predyiduschiy_diagnoz'],
								hiddenName: 'Disp_Diag_pid',
								id: 'PDSW_PredDiagCombo',
								listWidth: 600,
								tabIndex: TABINDEX_PERSDISPSW + 74,
								width: 500,
								xtype: 'swdiagcombo'
							}, {
								border: false,
								layout: 'column',
								items: [{
									border: false,
									layout: 'form',
									items: [{
										fieldLabel: lang['predyiduschiy_diagnoz_s'],
										hiddenName: 'Disp_PredDiag_Code_From',
										listWidth: 600,
										tabIndex: TABINDEX_PERSDISPSW + 75,
										valueField: 'Diag_Code',
										width: 233,
										xtype: 'swdiagcombo'
									}]
								}, {
									border: false,
									layout: 'form',
									labelWidth: 29,									
									items: [{
										fieldLabel: lang['po'],
										hiddenName: 'Disp_PredDiag_Code_To',
										listWidth: 600,
										tabIndex: TABINDEX_PERSDISPSW + 76,
										valueField: 'Diag_Code',
										width: 233,
										xtype: 'swdiagcombo'
									}]
								}]
							}, {
								beforeBlur: function() {
									// медитируем
									return true;
								},
								fieldLabel: lang['novyiy_diagnoz'],
								hiddenName: 'Disp_Diag_nid',
								id: 'PDSW_NewDiagCombo',
								listWidth: 600,
								tabIndex: TABINDEX_PERSDISPSW + 77,
								width: 500,
								xtype: 'swdiagcombo'
							}, {
								border: false,
								layout: 'column',
								items: [{
									border: false,
									layout: 'form',
									items: [{
										fieldLabel: lang['novyiy_diagnoz_s'],
										hiddenName: 'Disp_NewDiag_Code_From',
										listWidth: 600,
										tabIndex: TABINDEX_PERSDISPSW + 78,
										valueField: 'Diag_Code',
										width: 233,
										xtype: 'swdiagcombo'
									}]
								}, {
									border: false,
									layout: 'form',
									labelWidth: 29,									
									items: [{
										enableKeyEvents: true,
										fieldLabel: lang['po'],
										hiddenName: 'Disp_NewDiag_Code_To',
										listeners: {
											'keydown': function(inp, e) {
												if (!e.shiftKey && e.getKey() == e.TAB)
												{
													Ext.TaskMgr.start({
														run : function() {
															Ext.TaskMgr.stopAll();
															Ext.getCmp('PersonDispSearchGrid').focus();													
														},
														interval : 200
													});
													e.stopEvent();
												}
											}
										},
										listWidth: 600,
										tabIndex: TABINDEX_PERSDISPSW + 79,
										valueField: 'Diag_Code',
										width: 233,
										xtype: 'swdiagcombo'
									}]
								}]
							}]
						}
						]
					}),
					new sw.Promed.ViewFrame(
					{
						tbActions: true,
						actions:
						[
							{name: 'action_add', handler: function() { win.addPersonDisp(); }},
							{name: 'action_edit', handler: function() { win.editPersonDisp(); }},
							{name: 'action_view', handler: function() { win.viewPersonDisp(); }},
							{name: 'action_delete', handler: function() { win.deletePersonDisp(); }},
							{name: 'action_refresh'},
							{name: 'action_print',
								menuConfig: {
									print030: {text: 'Печать формы №030-4/у', name: 'print030', handler: function(){this.print030();}.createDelegate(this)}
								}
							},
							{
								name:'action_erdb',
								text: 'Поиск в ЭРДБ',
								hidden: getRegionNick()!='kz',
								icon: 'img/icons/search16.png',
								iconCls: 'x-btn-text',
								handler: function () {
									win.searchInERDB();
								}
							}
						],
						autoLoadData: false,
						dataUrl: C_SEARCH,
						id: 'PersonDispSearchGrid',
						focusOn: {name:'PDSW_SearchButton', type:'field'},
						pageSize: 100,
						paging: true,
						onEnter: function() {
							if(Ext.getCmp('PersonDispSearchGrid').ViewActions.action_edit.isDisabled())
							Ext.getCmp('PersonDispSearchGrid').ViewActions.action_view.execute();
						else
							Ext.getCmp('PersonDispSearchGrid').ViewActions.action_edit.execute();	
						},
						onDblClick: function() {
						if(Ext.getCmp('PersonDispSearchGrid').ViewActions.action_edit.isDisabled())
							Ext.getCmp('PersonDispSearchGrid').ViewActions.action_view.execute();
						else
							Ext.getCmp('PersonDispSearchGrid').ViewActions.action_edit.execute();							
						},
						onRowSelect: function(sm, index, record) {
							if(win.viewOnly == true){
								Ext.getCmp('PersonDispSearchGrid').setActionDisabled('action_view', false);
								Ext.getCmp('PersonDispSearchGrid').setActionDisabled('action_edit', true);
								Ext.getCmp('PersonDispSearchGrid').setActionDisabled('action_delete', true);
							}
							else
							{
								if ( record.get('IsOurLpu') == 1 )
								{
									Ext.getCmp('PersonDispSearchGrid').setActionDisabled('action_edit', true);
									Ext.getCmp('PersonDispSearchGrid').setActionDisabled('action_delete', true);
								}
								else
								{
									Ext.getCmp('PersonDispSearchGrid').setActionDisabled('action_edit', false);
									Ext.getCmp('PersonDispSearchGrid').setActionDisabled('action_delete', false);
								}
							}
							if(record.get('Diag_Code') && record.get('Diag_Code').substr(0,3) >= 'A15' && record.get('Diag_Code').substr(0,3) <= 'A19'){
								Ext.getCmp('PersonDispSearchGrid').ViewActions.action_print.menu.print030.setDisabled(false);
							} else {
								Ext.getCmp('PersonDispSearchGrid').ViewActions.action_print.menu.print030.setDisabled(true);
							}
						},
						region: 'center',
						root: 'data',
						totalProperty: 'totalCount',
						stringfields:
						[
							{name: 'PersonDisp_id', type: 'int', header: 'ID', key: true},
							{name: 'Person_id', type: 'int', hidden: true},
							{name: 'Server_id', type: 'int', hidden: true},
							{name: 'Person_Surname',  type: 'string', header: lang['familiya'], width: 200},
							{name: 'Person_Firname',  type: 'string', header: lang['imya'], width: 200},
							{name: 'Person_Secname',  type: 'string', header: lang['otchestvo'], width: 200},
							{name: 'Person_Birthday',  type: 'date', header: lang['data_rojdeniya'], renderer: Ext.util.Format.dateRenderer('d.m.Y')},
							{name: 'Diag_Code',  type: 'string', header: lang['diagnoz']},
							{name: 'PersonDisp_begDate',  type: 'date', header: lang['vzyat'], renderer: Ext.util.Format.dateRenderer('d.m.Y')},
							{name: 'PersonDisp_endDate',  type: 'date', header: lang['snyat'], renderer: Ext.util.Format.dateRenderer('d.m.Y')},
							{name: 'PersonDisp_NextDate',  type: 'date', header: lang['data_sled_yavki'], renderer: Ext.util.Format.dateRenderer('d.m.Y')},
							{name: 'PersonDisp_LastDate',  type: 'date', header: 'Дата последней явки', renderer: Ext.util.Format.dateRenderer('d.m.Y')},
							{name: 'LpuSection_Name',  type: 'string', header: lang['otdelenie']},
							{name: 'MedPersonal_FIO',  type: 'string', header: lang['postavivshiy_vrach'], width: 120},
							{name: 'MedPersonalHist_FIO',  type: 'string', header: 'Ответственный врач', width: 120},
							{name: 'Lpu_Nick',  type: 'string', header: lang['lpu']},
							{name: 'Sickness_Name',  type: 'string', header: lang['zabolevanie']},
                            {name: 'LpuRegion_Name',  type: 'string', header: lang['uchastok']},
							{name: 'Is7Noz',  type: 'checkbox', header: lang['7_noz']},
							{name: 'IsOurLpu',  type: 'int', hidden: true}
						],
						toolbar: true,
						onBeforeLoadData: function() {
							this.getButtonSearch().disable();
						}.createDelegate(this),
						onLoadData: function() {
							this.getButtonSearch().enable();
						}.createDelegate(this)
					})
				]})
			]

		});
		sw.Promed.swPersonDispSearchWindow.superclass.initComponent.apply(this, arguments);
	},
	keys: [{
		key: Ext.EventObject.INSERT,
		fn: function(e) {Ext.getCmp("PersonDispSearchWindow").addPersonDisp();},
		stopEvent: true
	}, {
		key: "0123456789",
		alt: true,
		fn: function(e) {Ext.getCmp("PersonDispFilterTabPanel").setActiveTab(Ext.getCmp("PersonDispFilterTabPanel").items.items[ e - 49 ]);},
		stopEvent: true
	}, {
		alt: true,
		fn: function(inp, e) {
			var current_window = Ext.getCmp('PersonDispSearchWindow');
			switch (e.getKey())
			{
				case Ext.EventObject.J:
					current_window.hide();
				break;
				case Ext.EventObject.C:
					current_window.doResetAll();
				break;
			}
		},
		key: [ Ext.EventObject.J, Ext.EventObject.C ],
		stopEvent: true
	}],
	layout: 'border',
        listeners: {
            'resize': function (win, nW, nH, oW, oH) {
                win.findById('PersonDispFilterTabPanel').setWidth(nW - 5);
                win.findById('PersonDispViewFilterForm').setWidth(nW - 5);
            }
        },
	maximizable: true,
	minHeight: 550,
	minWidth: 900,
	modal: false,
	plain: true,
	resizable: true,
	refreshPersonDispSearchGrid: function() {
		var form = this.findById('PersonDispViewFilterForm');
		if ( !form.isEmpty() ) {
			this.doSearch();
		}
	},
	show: function() {
		sw.Promed.swPersonDispSearchWindow.superclass.show.apply(this, arguments);

		this.restore();
		this.center();
		this.maximize();

		var grid = this.findById('PersonDispSearchGrid').ViewGridPanel;
		grid.getStore().removeAll();
		this.findById('PersonDispSearchGrid').addEmptyRecord(this.findById('PersonDispSearchGrid').getGrid().getStore());
		
		var form = this.findById('PersonDispViewFilterForm');
		
		// режим отображения формы
		this.listMode = false;

		this.doResetAll();

		this.viewOnly = false;
		this.ARMType = null;
		if(arguments[0])
		{
			if(arguments[0].viewOnly)
				this.viewOnly = arguments[0].viewOnly;

			if(arguments[0].ARMType){
				this.ARMType = arguments[0].ARMType;
			}
		}
		Ext.getCmp('PersonDispSearchGrid').setActionDisabled('action_add', this.viewOnly);
		
		this.setTitle(WND_POL_PERSDISPSEARCH);

		var tabPanel = this.findById('PersonDispFilterTabPanel');
		this.activeTabs();
		tabPanel.setActiveTab('PDFTP_FirstTab');
		form.getForm().findField('Person_Surname').focus(true, 200);
		form.getForm().findField('LpuRegionType_id').getStore().filterBy( //https://redmine.swan.perm.ru/issues/78988
			function(record)
			{
				//if (record.data.LpuRegionType_SysNick.inlist(['feld']) && getRegionNick() == 'perm')
				if (record.data.LpuRegionType_SysNick.inlist(['feld']) && getRegionNick().inlist(['perm','buryatiya','kareliya','khak','krym','ekb','ufa']))
					return false;
				else
					return true;
			}
		);
		// для печати списка
		form.getForm().getEl().dom.action = "/?c=Search&m=printSearchResults";
		form.getForm().getEl().dom.method = "post";
		form.getForm().getEl().dom.target = "_blank";
		form.getForm().standardSubmit = true;
	},
	showMessage: function(title, message, fn) {
		if ( !fn )
			fn = function(){};
		Ext.MessageBox.show({
			buttons: Ext.Msg.OK,
			fn: fn,
			icon: Ext.Msg.WARNING,
			msg: message,
			title: title
		});
	},
	title: WND_POL_PERSDISPSEARCH,
	viewPersonDisp: function() {
		var grid = this.findById('PersonDispSearchGrid').ViewGridPanel;
		var current_row = grid.getSelectionModel().getSelected();

		if ( !current_row ) {
			return;
		}

		if ( getWnd('swPersonDispEditWindow').isVisible() ) {
			sw.swMsg.alert(lang['soobschenie'], lang['okno_redaktirovaniya_dispansernoy_kartyi_patsienta_uje_otkryito']);
			return false;
		}
		
		var formParams = new Object();
		var params = new Object();

		formParams.Person_id = current_row.data.Person_id;
		formParams.PersonDisp_id = current_row.data.PersonDisp_id;
		formParams.Server_id = current_row.data.Server_id;

		if(this.ARMType){
			params.ARMType = this.ARMType;
		}

		params.action = 'view';
		params.callback = function() {
			this.refreshPersonDispSearchGrid();
		}.createDelegate(this);
		params.formParams = formParams;
		params.onHide = function() {
			//
		}.createDelegate(this);
		
		getWnd('swPersonDispEditWindow').show(params);
	},
	activeTabs: function(){
		var tabPanel = this.findById('PersonDispFilterTabPanel');
		if(tabPanel.items && tabPanel.items.items) {
			tabPanel.items.items.forEach(function(item, i, arr){
				tabPanel.setActiveTab(i);
			});
			tabPanel.setActiveTab(0);
		}
	},
	width: 900
});