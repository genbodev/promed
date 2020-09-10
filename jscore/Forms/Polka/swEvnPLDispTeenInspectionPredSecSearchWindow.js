/**
* swEvnPLDispTeenInspectionPredSecSearchWindow - окно поиска карты по предварительному осмотру несовершеннолетнего
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package    Polka
* @access     public
* @copyright  Copyright (c) 2009 Swan Ltd.
* @author     Марков 
* @version    май 2010
* @comment    Префикс для id компонентов EPLDTIPRESSW (EvnPLDispTeenInspectionPredSecSearchWindow)
*             tabIndex: TABINDEX_EPLDTIPRESSW = 9200;
*
*
* Использует: окно редактирования карты по предварительному осмотру несовершеннолетнего (swEvnPLDispTeenInspectionPredSecEditWindow)
*             окно поиска человека (swPersonSearchWindow)
*/
/*NO PARSE JSON*/
sw.Promed.swEvnPLDispTeenInspectionPredSecSearchWindow = Ext.extend(sw.Promed.BaseForm, 
{
	/* */
	codeRefresh: true,
	objectName: 'swEvnPLDispTeenInspectionPredSecSearchWindow',
	objectSrc: '/jscore/Forms/Polka/swEvnPLDispTeenInspectionPredSecSearchWindow.js',
	setEvnIsTransit: function() {
		if ( !lpuIsTransit() ) {
			return false;
		}

		var grid = this.findById('EPLDTIPRESSWEvnPLDispTeenInspectionPredSearchGrid').getGrid();

		if ( !grid || !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('EvnPLDispTeenInspection_id') || grid.getSelectionModel().getSelected().get('EvnPLDispTeenInspection_IsTransit') == 2 ) {
			return false;
		}

		var record = grid.getSelectionModel().getSelected();
		var Evn_IsTransit = 2;

		var params = {
			Evn_id: record.get('EvnPLDispTeenInspection_id'),
			Evn_IsTransit: Evn_IsTransit
		}

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: lang['ustanovka_priznaka_perehodnyiy_sluchay_mejdu_mo'] });
		loadMask.show();

		Ext.Ajax.request({
			callback: function(options, success, response) {
				loadMask.hide();

				if ( success ) {
					var response_obj = Ext.util.JSON.decode(response.responseText);

					if ( response_obj.success == false ) {
						sw.swMsg.alert(lang['oshibka'], response_obj.Error_Msg ? response_obj.Error_Msg : lang['oshibka_pri_ustanovke_priznaka_perehodnyiy_sluchay_mejdu_mo']);
					}
					else {
						record.set('EvnPLDispTeenInspection_IsTransit', Evn_IsTransit);
						record.commit();
						this.findById('EPLDTIPRESSWEvnPLDispTeenInspectionPredSearchGrid').onRowSelect(null, null, record);
					}
				}
				else {
					sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_ustanovke_priznaka_perehodnyiy_sluchay_mejdu_mo']);
				}
			}.createDelegate(this),
			params: params,
			url: C_SETEVNISTRANSIT
		});
	},
	onEvnPLDispTeenInspectionSaved: function(data)
	{
		var win = this;
		var evnpldispdop_grid = this.findById('EPLDTIPRESSWEvnPLDispTeenInspectionPredSearchGrid').getGrid();
		
		if ( !data || !data.evnPLDispTeenInspectionData ) {
			return false;
		}
		// Обновить запись в grid
		var record = evnpldispdop_grid.getStore().getById(data.evnPLDispTeenInspectionData.EvnPLDispTeenInspection_id);
		if ( record ) {
			var grid_fields = new Array();
			var i = 0;
			evnpldispdop_grid.getStore().fields.eachKey(function(key, item) {
				grid_fields.push(key);
			});
			for ( i = 0; i < grid_fields.length; i++ ) {
				record.set(grid_fields[i], data.evnPLDispTeenInspectionData[grid_fields[i]]);
			}
			record.commit();

			win.checkPrintCost();
		}
		else {
			if ( evnpldispdop_grid.getStore().getCount() == 1 && !evnpldispdop_grid.getStore().getAt(0).get('EvnPLDispTeenInspection_id') ) {
				evnpldispdop_grid.getStore().removeAll();
			}
			evnpldispdop_grid.getStore().loadData({'data': [ data.evnPLDispTeenInspectionData ]}, true);
		}
	},
	addEvnPLDD: function() 
	{
		var frm = this;

		if (getWnd('swPersonSearchWindow').isVisible())
		{
			Ext.Msg.alert(lang['soobschenie'], lang['okno_poiska_cheloveka_uje_otkryito']);
			return false;
		}

		if (getWnd('swEvnPLDispTeenInspectionPredSecEditWindow').isVisible())
		{
			Ext.Msg.alert(lang['soobschenie'], lang['okno_redaktirovaniya_kartyi_predvaritelnogo_osmotra_nesovershennoletnego_uje_otkryito']);
			return false;
		}
		var Year = this.findById('EPLDTIPRESSWYearCombo').getValue();
		
		getWnd('swPersonSearchWindow').show({
			onClose: function() {
				// Установить фокус на первое поле текущей вкладки
				var panel = Ext.getCmp('EPLDTIPRESSWSearchFilterTabbar').getActiveTab();
				var els=panel.findByType('textfield', false);
				if (els==undefined)
					els=panel.findByType('combo', false);
				var el=els[0];
				if (el!=undefined && el.focus)
					el.focus(true, 200);
			},
			onSelect: function(person_data) {
				// сначала проверим, можем ли мы добавлять карту на этого человека
				Ext.Ajax.request({
					url: '/?c=EvnPLDispTeenInspection&m=checkIfEvnPLDispTeenInspectionExists',
					callback: function(opt, success, response) {
						if (success && response.responseText != '')
						{
							var response_obj = Ext.util.JSON.decode(response.responseText);

							if ( response_obj.isEvnPLDispTeenInspectionExists == false )
							{
								if (Ext.isEmpty(response_obj.EvnPLDispTeenInspection_fid))
								{
									sw.swMsg.alert("Ошибка", "На данного пациента ещё не заведена карта первого этапа");
									return;
								}
								
								var params = {
									action: 'edit',
									EvnPLDispTeenInspection_fid: response_obj.EvnPLDispTeenInspection_fid,
									EducationInstitutionType_id: response_obj.EducationInstitutionType_id,
									Org_id: response_obj.Org_id,
									Person_id: person_data.Person_id,
									PersonEvn_id: person_data.PersonEvn_id,
									Server_id: person_data.Server_id,
									Year: Year
								};
								if (!Ext.isEmpty(response_obj.EducationInstitutionType_id)) {
									params.EducationInstitutionType_id = response_obj.EducationInstitutionType_id;
								}
								if (!Ext.isEmpty(response_obj.Org_id)) {
									params.Org_id = response_obj.Org_id;
								}
								params.callback = function(data) {
									frm.onEvnPLDispTeenInspectionSaved(data);
								};
								getWnd('swEvnPLDispTeenInspectionPredSecEditWindow').show(params);
								getWnd('swPersonSearchWindow').hide();
								return;
							}
							else
							{
								sw.swMsg.alert("Ошибка", "На данного пациента уже заведена карта диспансеризации несовершеннолетнего",
									function () {
										getWnd('swPersonSearchWindow').hide();
										frm.addEvnPLDD();
									}
								);
								return;
							}
						}
					},
					params: { Person_id: person_data.Person_id, DispClass_id: 11, EvnPLDisp_Year: Year }
				});				
			},
			searchMode: 'evnpldtipre',
			Year: Year
		});
	},
	begDate: null,
	begTime: null,
	buttonAlign: 'left',
	closable: true,
	closeAction: 'hide',
	collapsible: true,
	doDeleteEvnPLDD: function(options) {
		if (typeof options != 'object') {
			options = new Object();
		}

		var win = this;
		var grid = win.findById('EPLDTIPRESSWEvnPLDispTeenInspectionPredSearchGrid').getGrid();

		if ( !grid || !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('EvnPLDispTeenInspection_id') ) {
			return false;
		}

		var record = grid.getSelectionModel().getSelected();
		var evn_pl_dd_id = record.get('EvnPLDispTeenInspection_id');

		var params = {
			EvnPLDispTeenInspection_id: evn_pl_dd_id
		};

		if (options.ignoreCheckRegistry) {
			params.ignoreCheckRegistry = 1;
		}

		Ext.Ajax.request({
			callback: function(options, success, response) {
				if ( success ) {
					var response_obj = Ext.util.JSON.decode(response.responseText);

					if ( response_obj.success == false ) {
						sw.swMsg.alert(lang['oshibka'], response_obj.Error_Msg ? response_obj.Error_Msg : lang['oshibka_pri_udalenii_kartyi_dd']);
					}
					else if (response_obj.Alert_Msg) {
						sw.swMsg.show({
							icon: Ext.MessageBox.QUESTION,
							msg: response_obj.Alert_Msg + ' Продолжить?',
							title: lang['podtverjdenie'],
							buttons: Ext.Msg.YESNO,
							fn: function(buttonId, text, obj) {
								if ('yes' == buttonId) {
									options.ignoreCheckRegistry = true;
									win.doDeleteEvnPLDD(options);
								}
							}
						});
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
					sw.swMsg.alert(lang['oshibka'], lang['pri_udalenii_kartyi_dd_voznikli_oshibki']);
				}
			},
			params: params,
			url: '/?c=EvnPLDispTeenInspection&m=deleteEvnPLDispTeenInspection'
		});
	},
	deleteEvnPLDD: function() {
		var win = this;
		var grid = win.findById('EPLDTIPRESSWEvnPLDispTeenInspectionPredSearchGrid').getGrid();

		if ( !grid || !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('EvnPLDispTeenInspection_id') ) {
			return false;
		}

		var record = grid.getSelectionModel().getSelected();
		var evn_pl_dd_id = record.get('EvnPLDispTeenInspection_id');

		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function(buttonId, text, obj) {
				if ( buttonId == 'yes' ) {
					win.doDeleteEvnPLDD();
				}
			},
			icon: Ext.MessageBox.QUESTION,
			msg: lang['udalit_kartu_dd'],
			title: lang['vopros']
		});
	},
	doReset: function() {
		var frm = this;
		var filter_form = frm.findById('EPLDTIPRESSWEvnPLDispTeenInspectionPredSearchFilterForm');
		filter_form.getForm().reset();
		frm.findById('EPLDTIPRESSWEvnPLDispTeenInspectionPredSearchGrid').getGrid().getStore().removeAll();
	},
	searchInProgress: false,
	doSearch: function(params) {
		if (this.searchInProgress) {
			log(lang['poisk_uje_vyipolnyaetsya']);
			return false;
		} else {
			this.searchInProgress = true;
		}
		var frm = this;

		if ( params && params['soc_card_id'] )
			var soc_card_id = params['soc_card_id'];
	
		if ( this.isStream )
		{
			this.doStreamInputSearch();
			return true;
		}
		var frm = this;
		var filter_form = frm.findById('EPLDTIPRESSWEvnPLDispTeenInspectionPredSearchFilterForm');
		
		if ( filter_form.isEmpty() ) {
			frm.searchInProgress = false;
			sw.swMsg.alert(lang['oshibka'], lang['ne_zapolneno_ni_odno_pole'], function() {
			});
			return false;
		}
		
		var evnpldispdop_grid = frm.findById('EPLDTIPRESSWEvnPLDispTeenInspectionPredSearchGrid').ViewGridPanel;

		var arr = filter_form.find('disabled', true);
		var params = filter_form.getForm().getValues();

		for (i = 0; i < arr.length; i++)
		{
			if (arr[i].getValue)
			{
				if (arr[i].hiddenName != undefined)
					params[arr[i].hiddenName] = arr[i].getValue();
				else if (arr[i].name != undefined)
					params[arr[i].name] = arr[i].getValue();
			}
		}
		
		// фильтрация по году 
		var Year = Ext.getCmp('EPLDTIPRESSWYearCombo').getValue();
		if (Year>0)
		{
			if (filter_form.getForm().findField('EvnPLDispTeenInspection_setDate_Range').getValue1()=='')
			{
				params['EvnPLDispTeenInspection_setDate_Range'] = ('01.01.'+Year+' - '+'31.12.'+Year);
			}
		}
		
		if (filter_form.getForm().isValid())
		{
			if ( soc_card_id )
			{
				var params = {
					soc_card_id: soc_card_id,
					SearchFormType: params.SearchFormType
				};
			}
			params.start = 0;
			params.limit = 100;

			if (!Ext.isEmpty(params.autoLoadArchiveRecords)) {
				frm.findById('EPLDTIPRESSWEvnPLDispTeenInspectionPredSearchGrid').showArchive = true;
			} else {
				frm.findById('EPLDTIPRESSWEvnPLDispTeenInspectionPredSearchGrid').showArchive = false;
			}

			params.DispClass_id = 11;
			params.SearchFormType = "EvnPLDispTeenInspectionPred",
			evnpldispdop_grid.getStore().removeAll();
			evnpldispdop_grid.getStore().baseParams = params;
			evnpldispdop_grid.getStore().load({
				params: params,
				callback: function (){
					frm.searchInProgress = false;
				}
			});
		}
		else {
			frm.searchInProgress = false;
			sw.swMsg.alert('Поиск', 'Проверьте правильность заполнения полей на форме поиска');
		}
	},
	doStreamInputSearch: function() {
		var grid = this.findById('EPLDTIPRESSWEvnPLDispTeenInspectionPredSearchGrid').ViewGridPanel;
		var form = this.findById('EPLDTIPRESSWEvnPLDispTeenInspectionPredSearchFilterForm');
		var frm = this;
		var params = {};
		params.EvnPLDispTeenInspectionStream_begDate = this.begDate;
		params.EvnPLDispTeenInspectionStream_begTime = this.begTime;
		if ( !params.EvnPLDispTeenInspectionStream_begDate && !params.EvnPLDispTeenInspectionStream_begTime )
			this.getBegDateTime();
		else
		{
			params.start = 0;
			params.limit = 100;
			params.SearchFormType = "EvnPLDispTeenInspectionPred",
			grid.getStore().removeAll();
			grid.getStore().baseParams = params;
			grid.getStore().load({
				callback: function (){
					frm.searchInProgress = false;
				},
				params: params
			});
		}
	},
	draggable: true,
	getBegDateTime: function() {
		var frm = this;
		Ext.Ajax.request({
			url: C_LOAD_CURTIME,
			callback: function(opt, success, response) {
				if (success && response.responseText != '')
				{
					frm.searchInProgress = false;
					var response_obj = Ext.util.JSON.decode(response.responseText);

					frm.begDate = response_obj.begDate;
					frm.begTime = response_obj.begTime;
					if ( frm.isStream ) {
						frm.doStreamInputSearch();
					}
					frm.findById('EPLDTIPRESSWStream_begDateTime').setValue(response_obj.begDate + ' ' + response_obj.begTime);
				}
			}
		});
	},
	height: 550,
	id: 'EvnPLDispTeenInspectionPredSecSearchWindow',
	getButtonSearch: function() {
		// TODO: правильно юзать scope кнопки
		return Ext.getCmp('EPLDTIPRESSWSearchButton');
	},
	printCost: function() {
		var grid = this.findById('EPLDTIPRESSWEvnPLDispTeenInspectionPredSearchGrid').getGrid();
		var selected_record = grid.getSelectionModel().getSelected();
		if (selected_record && selected_record.get('EvnPLDispTeenInspection_id')) {
			getWnd('swCostPrintWindow').show({
				Evn_id: selected_record.get('EvnPLDispTeenInspection_id'),
				type: 'EvnPLDispTeenInspection',
				callback: function() {
					grid.getStore().reload();
				}
			});
		}
	},
	checkPrintCost: function() {
		// Печать справки только для закрытых случаев
		var grid = this.findById('EPLDTIPRESSWEvnPLDispTeenInspectionPredSearchGrid').getGrid();
		var menuPrint = this.findById('EPLDTIPRESSWEvnPLDispTeenInspectionPredSearchGrid').getAction('action_print').menu;
		if (menuPrint && menuPrint.printCost) {
			menuPrint.printCost.setDisabled(true);
			var selected_record = grid.getSelectionModel().getSelected();
			if (selected_record && selected_record.get('EvnPLDispTeenInspection_id')) {
				menuPrint.printCost.setDisabled(selected_record.get('EvnPLDispTeenInspection_IsFinish') != lang['da']);
			}
		}
	},
	initComponent: function() 
	{
		var frm = this;
		
		this.EditForm = new Ext.Panel(
		{
			autoHeight: true,
			id: 'EPLDTIPRESSWSearchFilterPanel',
			region: 'north',
			layout: 'form',
			border: false,
			labelWidth: 120,
			items: 
			[{
				bodyStyle:'padding:3px; padding-left:5px;',
				layout: 'form',
				xtype: 'panel',
				border: false,
				//frame: true,
				labelWidth: 125,
				items:
				[{
					xtype: 'swbaselocalcombo',
					mode: 'local',
					fieldLabel: lang['god'],
					store: new Ext.data.JsonStore(
					{
						key: 'EvnPLDisp_Year',
						autoLoad: false,
						fields:
						[
							{name:'EvnPLDisp_Year',type: 'int'},
							{name:'count', type: 'int'}
						],
						url: C_EPLD_LOAD_YEARS
					}),
					id: 'EPLDTIPRESSWYearCombo',
					hiddenName: 'EvnPLDisp_Year',
					tpl:'<tpl for="."><div class="x-combo-list-item"><table border="0" width="100%"><tr><td style="width: 40px; color: red">{EvnPLDisp_Year}</td>'+
							'<td style="text-align: right"><b>{count}<b></td></tr></table>'+
							'</div></tpl>',
					region: 'north',
					valueField: 'EvnPLDisp_Year',
					displayField: 'EvnPLDisp_Year',
					editable: false,
					tabIndex: TABINDEX_EPLDTIPRESSW+95,
					enableKeyEvents: true,
					listeners: 
					{
						'keydown': function(combo, e)
						{
							if ( e.getKey() == Ext.EventObject.ENTER )
							{
								e.stopEvent();
								frm.doSearch();
							}
							if (e.shiftKey == false && e.getKey() == Ext.EventObject.TAB)
							{
								e.stopEvent();
								if ( Ext.getCmp('EvnPLDispTeenInspectionPredSecSearchWindow').isStream )
								{
									Ext.TaskMgr.start(
									{
										run : function() 
										{
											Ext.TaskMgr.stopAll();
											Ext.getCmp('EPLDTIPRESSWEvnPLDispTeenInspectionPredSearchGrid').focus();													
										},
										interval : 200
									});
									return true;
								}
								var panel = Ext.getCmp('EPLDTIPRESSWSearchFilterTabbar').getActiveTab();
								var els=panel.findByType('textfield', false);
								if (els==undefined)
									els=panel.findByType('combo', false);
								var el=els[0];
								if (el!=undefined && el.focus)
									el.focus(true, 200);
							}
						}
					}
				},
				{
					disabled: true,
					fieldLabel: lang['data_nachala_vvoda'],
					id: 'EPLDTIPRESSWStream_begDateTime',
					width: 165,
					xtype: 'textfield',
					tabIndex: TABINDEX_EPLDTIPRESSW+57
				}]
			},
			getBaseSearchFiltersFrame(
			{
				useArchive: 1,
				id: 'EPLDTIPRESSWEvnPLDispTeenInspectionPredSearchFilterForm',
				ownerWindow: this,
				region: 'north',
				searchFormType: 'EvnPLDispTeenInspectionPred',
				tabIndexBase: TABINDEX_EPLDTIPRESSW,
				tabPanelId: 'EPLDTIPRESSWSearchFilterTabbar',
				tabGridId: 'EPLDTIPRESSWEvnPLDispTeenInspectionPredSearchGrid',
				tabs: 
				[{
					autoHeight: true,
					bodyStyle: 'margin-top: 5px;',
					border: false,
					labelWidth: 200,
					id: 'EPLDTIPRESS_FirstTab',
					layout: 'form',
					listeners: 
					{
						'activate': function(panel) 
						{
							var form = this.findById('EPLDTIPRESSWEvnPLDispTeenInspectionPredSearchFilterForm');
							form.getForm().findField('EvnPLDispTeenInspection_setDate').focus(400, true);									
						}.createDelegate(this)
					},
							title: '<u>6</u>. Предварительные осмотры',
							items: [{
								hidden: !getRegionNick().inlist([ 'buryatiya', 'krym' ]),
								layout: 'form',
								border: false,
								items: [{
									hiddenName: 'UslugaComplex_id',
									width: 400,
									fieldLabel: lang['usluga_dispanserizatsii'],
									dispOnly: true,
									DispClass_id: 11,
									nonDispOnly: false,
									xtype: 'swuslugacomplexnewcombo'
								}]
							}, {
								border: false,
								layout: 'column',
								items: [{
									border: false,
									layout: 'form',									
									items: [{
										fieldLabel: lang['data_nachala'],
										name: 'EvnPLDispTeenInspection_setDate',
										plugins: [
											new Ext.ux.InputTextMask('99.99.9999', false)
										],
										tabIndex: TABINDEX_EPLDTIPRESSW + 59,										
										width: 100,
										xtype: 'swdatefield'
									}]
								}, {
									border: false,
									layout: 'form',
									labelWidth: 225,									
									items: [{
										fieldLabel: lang['diapazon_dat_nachala'],
										name: 'EvnPLDispTeenInspection_setDate_Range',
										tabIndex: TABINDEX_EPLDTIPRESSW + 60,
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
										fieldLabel: lang['data_okonchaniya'],
										name: 'EvnPLDispTeenInspection_disDate',
										plugins: [
											new Ext.ux.InputTextMask('99.99.9999', false)
										],
										tabIndex: TABINDEX_EPLDTIPRESSW + 61,										
										width: 100,
										xtype: 'swdatefield'
									}]
								}, {
									border: false,
									layout: 'form',
									labelWidth: 225,									
									items: [{
										fieldLabel: lang['diapazon_dat_okonchaniya'],
										name: 'EvnPLDispTeenInspection_disDate_Range',
										tabIndex: TABINDEX_EPLDTIPRESSW + 62,
										plugins: [
											new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)
										],
										width: 170,
										xtype: 'daterangefield'
									}]
								}]
							}, {
								allowDecimal: false,
								allowNegative: false,
								enableKeyEvents: true,
								fieldLabel: lang['sluchay_zakonchen'],
								hiddenName: 'EvnPLDispTeenInspection_IsFinish',
								listeners: {
									'keydown': function(combo, e) {
										if ( !e.shiftKey && e.getKey() == e.TAB )
										{
											Ext.TaskMgr.start({
												run : function() {
													Ext.TaskMgr.stopAll();
													Ext.getCmp('EPLDTIPRESSWEvnPLDispTeenInspectionPredSearchGrid').focus();													
												},
												interval : 200
											});
										}
									}
								},
								tabIndex: TABINDEX_EPLDTIPRESSW + 66,
								width: 100,
								xtype: 'swyesnocombo'
							}, {
								fieldLabel: lang['obslujen_mobilnoy_brigadoy'],
								hiddenName: 'EvnPLDispTeenInspection_isMobile',
								width: 100,
								xtype: 'swyesnocombo'
							},
							{
								allowBlank: true,
								enableKeyEvents: true,
								fieldLabel: lang['gruppa_zdorovya'],
								/*listeners: {
									'render': function(combo) {
										combo.getStore().load();
									}
								},*/
								hiddenName: 'EvnPLDispTeenInspection_HealthKind_id',
								validateOnBlur: false,
								width: 100,
								xtype: 'swhealthkindcombo'
							}, {
								comboSubject: 'HealthGroupType',
								fieldLabel: 'Медицинская группа для занятий физ.культурой до проведения обследования',
								hiddenName: 'HealthGroupType_oid',
								lastQuery: '',
								width: 300,
								xtype: 'swcommonsprcombo'
							}, {
								comboSubject: 'HealthGroupType',
								fieldLabel: lang['meditsinskaya_gruppa_dlya_zanyatiy_fiz_kulturoy'],
								hiddenName: 'HealthGroupType_id',
								lastQuery: '',
								width: 300,
								xtype: 'swcommonsprcombo'
							},
							{
								fieldLabel: lang['sluchay_oplachen'],
								tabIndex: TABINDEX_EPLDO13SW + 68,
								hiddenName: 'EvnPLDispTeenInspection_isPaid',
								width: 100,
								xtype: 'swyesnocombo'
							}
							]
						}]
					})]
		});
	
		Ext.apply(this, 
		{
			items: 
			[
				this.EditForm,
			new sw.Promed.ViewFrame({
				useArchive: 1,
				actions: [
					{ name: 'action_add', handler: function() { Ext.getCmp('EvnPLDispTeenInspectionPredSecSearchWindow').addEvnPLDD(); } },
					{ name: 'action_edit', handler: function() { Ext.getCmp('EvnPLDispTeenInspectionPredSecSearchWindow').openEvnPLDDSecEditWindow('edit'); } },
					{ name: 'action_view', handler: function() { Ext.getCmp('EvnPLDispTeenInspectionPredSecSearchWindow').openEvnPLDDSecEditWindow('view'); } },
					{ name: 'action_delete', handler: function() { Ext.getCmp('EvnPLDispTeenInspectionPredSecSearchWindow').deleteEvnPLDD(); } },
					{ name: 'action_refresh', handler: function() { Ext.getCmp('EvnPLDispTeenInspectionPredSecSearchWindow').refreshEvnPLDDList(); } },
					{ name: 'action_print', menuConfig: {
						printCost: {name: 'printCost', hidden: ! getRegionNick().inlist(['perm', 'kz', 'ufa']), text: langs('Справка о стоимости лечения'), handler: function () { frm.printCost() }}
					}}
				],
				autoExpandColumn: 'autoexpand',
				autoLoadData: false,
				dataUrl: C_SEARCH,
				focusOn: {
					name: 'EPLDTIPRESSWSearchButton', type: 'other'
				},
				id: 'EPLDTIPRESSWEvnPLDispTeenInspectionPredSearchGrid',
				layout: 'fit',
				object: 'EvnPLDD',
				pageSize: 100,
				paging: true,
				region: 'center',
				root: 'data',
				totalProperty: 'totalCount', 
				onBeforeLoadData: function() {
					this.getButtonSearch().disable();
				}.createDelegate(this),
				onLoadData: function() {
					this.getButtonSearch().enable();
				}.createDelegate(this),
				title: '',
				toolbar: true,
				stringfields: [
					{ name: 'EvnPLDispTeenInspection_id', type: 'int', header: 'ID', key: true },
					{ name: 'EvnPLDispTeenInspection_IsTransit', type: 'int', hidden: true },
					{ name: 'EvnPLDispTeenInspection_fid', type: 'int', hidden: true },
					{ name: 'Person_id', type: 'int', hidden: true },
					{ name: 'Server_id', type: 'int', hidden: true },
					{ name: 'UslugaComplex_Name', type: 'string', hidden: !getRegionNick().inlist([ 'buryatiya', 'krym' ]), header: langs('Услуга диспансеризации'), width: 150 },
					{ name: 'Person_Surname', id: 'autoexpand', type: 'string', header: langs('Фамилия'), width: 150 },
					{ name: 'Person_Firname', type: 'string', header: langs('Имя'), width: 150 },
					{ name: 'Person_Secname', type: 'string', header: langs('Отчество'), width: 150 },
					{ name: 'Person_Birthday', type: 'date', format: 'd.m.Y', header: langs('Д/р') },
					{ name: 'Sex_Name',  type: 'string', header: langs('Пол') },
					{ name: 'EvnPLDispTeenInspection_setDate', type: 'date', format: 'd.m.Y', header: langs('Дата начала') },
					{ name: 'EvnPLDispTeenInspection_disDate', type: 'date', format: 'd.m.Y', header: langs('Дата окончания') },
					{ name: 'EvnPLDispTeenInspection_IsFinish', type: 'string', header: langs('Закончен'), width: 60 },
					{ name: 'EvnPLDispTeenInspection_HealthKind_Name', type: 'string', header: langs('Группа здоровья') },
					{ name: 'EvnCostPrint_setDT', type: 'date', header: langs('Дата выдачи справки/отказа'), width: 150, hidden: ! getRegionNick().inlist(['perm', 'kz', 'ufa']) },
					{ name: 'EvnCostPrint_IsNoPrintText', type: 'string', header: langs('Справка о стоимости лечения'), width: 150, hidden: ! getRegionNick().inlist(['perm', 'kz', 'ufa']) }
				],
				onRowSelect: function(sm, index, record) {
					if(frm.viewOnly == true)
					{
						this.getAction('action_view').setDisabled(false);
						this.getAction('action_edit').setDisabled(true);
						this.getAction('action_delete').setDisabled(true);
					}
					else
					{
						// Запретить редактирование/удаление архивных записей
						if (getGlobalOptions().archive_database_enable) {
							this.getAction('action_edit').setDisabled(record.get('archiveRecord') == 1);
							this.getAction('action_delete').setDisabled(record.get('archiveRecord') == 1);
						}

						if ( record.get('EvnPLDispTeenInspection_id') ) {
							this.setActionDisabled('action_setevnistransit', !(record.get('EvnPLDispTeenInspection_IsTransit') == 1));
						}
						else {
							this.setActionDisabled('action_setevnistransit', true);
						}
					}	
					frm.checkPrintCost();
				}
			})],
			buttons: [{
				handler: function() {
					this.doSearch();
				}.createDelegate(this),
				iconCls: 'search16',
				id: 'EPLDTIPRESSWSearchButton',
				tabIndex: TABINDEX_EPLDTIPRESSW+90,
				text: BTN_FRMSEARCH
			}, {
				handler: function() {
					this.doReset();
				}.createDelegate(this),
				iconCls: 'resetsearch16',
				tabIndex: TABINDEX_EPLDTIPRESSW+91,
				text: BTN_FRMRESET
			}, {
				id: 'mode_button',
				hidden: true,
				disabled: true,
				handler: function() {
					if ( this.ownerCt.isStream == false )
					{
						this.ownerCt.setStreamInputMode();
					}
					else
					{
						this.ownerCt.setSearchMode();
					}
				},
				tabIndex: TABINDEX_EPLDTIPRESSW+92,
				text: "Режим потокового ввода"
			}, {
				disabled: false,
				handler: function() 
				{
					if(frm.viewOnly == true)
						frm.openEvnPLDDEditWindow('view');
					else
						frm.openEvnPLDDEditWindow('edit');
				},
				tabIndex: TABINDEX_EPLDTIPRESSW + 93,
				text: lang['karta_1_etap']
			},
			'-',
			HelpButton(this, TABINDEX_EPLDTIPRESSW + 94),
			{
				handler: function() {
					this.hide();
				}.createDelegate(this),
				iconCls: 'cancel16',
				tabIndex: TABINDEX_EPLDTIPRESSW+95,
				text: BTN_FRMCANCEL,
				onTabAction: function() {
					Ext.getCmp('EPLDTIPRESSWYearCombo').focus(true, 200);
				},
				onShiftTabAction: function() {
					Ext.getCmp('mode_button').focus(true, 200);
				}
			}
			]
		});
		sw.Promed.swEvnPLDispTeenInspectionPredSecSearchWindow.superclass.initComponent.apply(this, arguments);
	},
	isStream: null, // потоковый ввод или поиск?
	keys: [{
		alt: true,
		fn: function(inp, e) {
			var frm = Ext.getCmp('EvnPLDispTeenInspectionPredSecSearchWindow');
			var search_filter_tabbar = frm.findById('EPLDTIPRESSWSearchFilterTabbar');

			switch (e.getKey())
			{
				case Ext.EventObject.C:
					frm.doReset();
					break;
				case Ext.EventObject.J:
					frm.hide();
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
	}, {
		fn: function(inp, e) {
			var frm = Ext.getCmp('EvnPLDispTeenInspectionPredSecSearchWindow');
			switch (e.getKey())
			{
				case Ext.EventObject.INSERT:
					frm.addEvnPLDD();
					break;
			}
		},
		key: [
			Ext.EventObject.INSERT
		],
		stopEvent: true
	}],
	layout: 'border',
	loadYearsCombo: function () {
		var years_combo = this.findById('EPLDTIPRESSWYearCombo');
		if ( years_combo.getStore().getCount() == 0 ) {
			years_combo.getStore().load({
				params: {
					DispClass_id: 11
				},
				callback: function() {
					var date = new Date();
					var year = date.getFullYear();
					years_combo.setValue(year);
					years_combo.focus(true, 500);
				}
			});
		}
		else
		{
			var date = new Date();
					var year = date.getFullYear();
					years_combo.setValue(year);
					years_combo.focus(true, 500);
		}
	},
	maximizable: true,
	minHeight: 550,
	minWidth: 800,
	modal: false,
	openEvnPLDDEditWindow: function(action) {
		var frm = this;
		var evnpldispdop_grid = frm.findById('EPLDTIPRESSWEvnPLDispTeenInspectionPredSearchGrid').ViewGridPanel;

		if (getWnd('swEvnPLDispTeenInspectionPredEditWindow').isVisible())
		{
			Ext.Msg.alert(lang['soobschenie'], lang['okno_redaktirovaniya_kartyi_predvaritelnogo_osmotra_nesovershennoletnego_uje_otkryito']);
			return false;
		}

		if (!evnpldispdop_grid.getSelectionModel().getSelected())
		{
			return false;
		}

		var record = evnpldispdop_grid.getSelectionModel().getSelected();

		var evnpldispdop_id = record.data.EvnPLDispTeenInspection_fid;
		var person_id = record.data.Person_id;
		var server_id = record.data.Server_id;

		if (evnpldispdop_id > 0 && person_id > 0 && server_id >= 0)
		{
			var params = {
				action: action,
				EvnPLDispTeenInspection_id: evnpldispdop_id,
				onHide: Ext.emptyFn,
				callback: function(data) {
					// frm.onEvnPLDispTeenInspectionSaved(data); это не нужно
				},
				Person_id: person_id,
				Server_id: server_id
			};
			if (getGlobalOptions().archive_database_enable) {
				params.archiveRecord = record.get('archiveRecord');
			}
			getWnd('swEvnPLDispTeenInspectionPredEditWindow').show(params);
		}
	},
	openEvnPLDDSecEditWindow: function(action) {
		var frm = this;
		var evnpldispdop_grid = frm.findById('EPLDTIPRESSWEvnPLDispTeenInspectionPredSearchGrid').ViewGridPanel;

		if (getWnd('swEvnPLDispTeenInspectionPredSecEditWindow').isVisible())
		{
			Ext.Msg.alert(lang['soobschenie'], lang['okno_redaktirovaniya_kartyi_predvaritelnogo_osmotra_nesovershennoletnego_uje_otkryito']);
			return false;
		}

		if (!evnpldispdop_grid.getSelectionModel().getSelected())
		{
			return false;
		}

		var record = evnpldispdop_grid.getSelectionModel().getSelected();

		var evnpldispdop_id = record.data.EvnPLDispTeenInspection_id;
		var person_id = record.data.Person_id;
		var server_id = record.data.Server_id;

		if (evnpldispdop_id > 0 && person_id > 0 && server_id >= 0)
		{
			var params = {
				action: action,
				EvnPLDispTeenInspection_id: evnpldispdop_id,
				onHide: Ext.emptyFn,
				callback: function(data) {
					frm.onEvnPLDispTeenInspectionSaved(data);
				},
				Person_id: person_id,
				Server_id: server_id
			};
			if (getGlobalOptions().archive_database_enable) {
				params.archiveRecord = record.get('archiveRecord');
			}
			getWnd('swEvnPLDispTeenInspectionPredSecEditWindow').show(params);
		}
	},
	plain: true,
	refreshEvnPLDDList: function(action) {
		var frm = this;
		var evnpldispdop_grid = frm.findById('EPLDTIPRESSWEvnPLDispTeenInspectionPredSearchGrid').ViewGridPanel;
		if ( this.isStream ) {
			this.doStreamInputSearch();
			this.loadYearsCombo();
		}
		else {
			this.doSearch();
		}
	},
	resizable: true,
	setSearchMode: function()
	{
		var button = this.buttons[2];
		this.buttons[0].enable();
		this.buttons[1].enable();
		button.setText(lang['rejim_potokovogo_vvoda']);
		this.setTitle(WND_POL_EPLDTIPRESSEARCH);
		Ext.getCmp('EPLDTIPRESSWEvnPLDispTeenInspectionPredSearchFilterForm').setHeight(280);
		this.findById('EPLDTIPRESSWEvnPLDispTeenInspectionPredSearchFilterForm').show();	
		this.doLayout();		
		this.isStream = false;
		if ( this.findById('EPLDTIPRESSWYearCombo').getStore().getCount() > 0 )
			this.findById('EPLDTIPRESSWYearCombo').focus(true, 100);

	},
	getFilterForm: function() {
		return this.findById('EPLDTIPRESSWEvnPLDispTeenInspectionPredSearchFilterForm');
	},
	setStreamInputMode: function()
	{
		var button = this.buttons[2];
		this.buttons[0].disable();
		this.buttons[1].disable();		
		button.setText('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Режим поиска&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;');
		var grid = this.findById('EPLDTIPRESSWEvnPLDispTeenInspectionPredSearchGrid').ViewGridPanel;
		grid.getStore().removeAll();
		this.setTitle(WND_POL_EPLDTIPRESSTREAM);
		this.findById('EPLDTIPRESSWEvnPLDispTeenInspectionPredSearchFilterForm').hide();
		Ext.getCmp('EPLDTIPRESSWEvnPLDispTeenInspectionPredSearchFilterForm').setHeight(25);
		this.doLayout();
		this.isStream = true;
		this.doStreamInputSearch();
		if ( this.findById('EPLDTIPRESSWYearCombo').getStore().getCount() > 0 )
			this.findById('EPLDTIPRESSWYearCombo').focus(true, 100);
	},
	show: function() {
		sw.Promed.swEvnPLDispTeenInspectionPredSecSearchWindow.superclass.show.apply(this, arguments);
		if ( arguments[0] != undefined && arguments[0].mode != undefined )
		{
			if ( arguments[0].mode == 'stream' )
			{
				this.setSearchMode();
				this.findById('EPLDTIPRESSWSearchFilterTabbar').setActiveTab('EPLDTIPRESS_FirstTab');
				this.setStreamInputMode();
			}
			else
				this.setSearchMode();
		}
		else
			this.setSearchMode();
		this.getBegDateTime();

		if ( !this.findById('EPLDTIPRESSWEvnPLDispTeenInspectionPredSearchGrid').getAction('action_setevnistransit') ) {
			this.findById('EPLDTIPRESSWEvnPLDispTeenInspectionPredSearchGrid').addActions({
				disabled: true,
				handler: function() {
					this.setEvnIsTransit();
				}.createDelegate(this),
				iconCls: 'actions16',
				id: this.id + 'action_setevnistransit',
				name: 'action_setevnistransit',
				text: lang['perehodnyiy_sluchay']
			});
		}

		this.findById('EPLDTIPRESSWEvnPLDispTeenInspectionPredSearchGrid').setActionHidden('action_setevnistransit', !lpuIsTransit());

		this.restore();
		this.center();
		this.maximize();
		this.doReset();

		this.viewOnly = false;
		if(arguments[0])
		{
			if(arguments[0].viewOnly)
				this.viewOnly = arguments[0].viewOnly;
		}
		this.findById('EPLDTIPRESSWEvnPLDispTeenInspectionPredSearchGrid').setActionDisabled('action_add', this.viewOnly);
		this.findById('EPLDTIPRESSWEvnPLDispTeenInspectionPredSearchGrid').setActionDisabled('action_edit', this.viewOnly);
		this.findById('EPLDTIPRESSWEvnPLDispTeenInspectionPredSearchGrid').setActionDisabled('action_delete', this.viewOnly);

		this.loadYearsCombo();
		var form = this.findById('EPLDTIPRESSWEvnPLDispTeenInspectionPredSearchFilterForm');
		form.getForm().findField('EvnPLDispTeenInspection_HealthKind_id').getStore().clearFilter();
		form.getForm().findField('EvnPLDispTeenInspection_HealthKind_id').lastQuery = '';
		form.getForm().findField('EvnPLDispTeenInspection_HealthKind_id').getStore().filterBy(function(rec) {
			if(!rec.get('HealthKind_Code').inlist(['6','7']))
			{
				return true;
			}
			return false;
		});
		this.findById('EPLDTIPRESSWSearchFilterTabbar').setActiveTab('EPLDTIPRESS_FirstTab');
	},
	title: WND_POL_EPLDTIPRESSEARCH,
	width: 800
});